export function registerLearningLang(Alpine) {
    const storageKey = 'erp-locale';

    Alpine.store('learningLang', {
        code: 'en',
        switching: false,

        init() {
            this.code = window.erpLocale === 'bn' ? 'bn' : 'en';

            try {
                localStorage.setItem(storageKey, this.code);
            } catch (error) {
                // ignore
            }
        },

        isBn() {
            return this.code === 'bn';
        },

        async setLocale(nextCode) {
            if (this.switching || (nextCode !== 'en' && nextCode !== 'bn') || this.code === nextCode) {
                return;
            }

            const url = window.erpLocaleUrl;
            if (! url) {
                this.code = nextCode;
                return;
            }

            this.switching = true;

            try {
                const formData = new FormData();
                formData.append('locale', nextCode);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (! response.ok) {
                    throw new Error('Locale switch failed');
                }

                try {
                    localStorage.setItem(storageKey, nextCode);
                } catch (error) {
                    // ignore
                }

                window.location.reload();
            } catch (error) {
                this.switching = false;
            }
        },

        toggle() {
            this.setLocale(this.code === 'en' ? 'bn' : 'en');
        },

        pick(pair) {
            if (! pair || typeof pair !== 'object') {
                return '';
            }

            return this.code === 'bn' ? (pair.bn ?? pair.en ?? '') : (pair.en ?? pair.bn ?? '');
        },
    });
}

export function normalizeMermaidSource(source) {
    if (! source || typeof source !== 'string') {
        return '';
    }

    let normalized = source.trim();

    normalized = normalized.replace(/([A-Za-z0-9_]+)\[([^\]"]+)\]/g, (match, nodeId, label) => {
        const trimmed = label.trim();
        const needsQuotes = /[\/&+()]/.test(trimmed) || /[^\x00-\x7F]/.test(trimmed) || trimmed.includes('"');

        if (! needsQuotes) {
            return match;
        }

        const safeLabel = trimmed.replace(/"/g, "'");
        return `${nodeId}["${safeLabel}"]`;
    });

    return normalized;
}

export function learningHub(modules, ui, roles, initialModule, defaultRole, learningPath) {
    const progressKey = 'erp-learning-progress-v2';
    const roleKey = 'erp-learning-role';

    return {
        modules: modules ?? [],
        ui: ui ?? {},
        roles: roles ?? [],
        learningPath: learningPath ?? [],
        view: 'catalog',
        courseSlug: null,
        lessonIndex: 0,
        activeRole: defaultRole ?? 'all',
        search: '',
        openFaq: null,
        progress: { courses: {} },
        flowLoading: false,
        flowError: '',
        quizAnswers: {},
        quizSubmitted: false,
        quizScore: 0,
        quizPassed: false,
        glossarySearch: '',

        publishAssistantContext() {
            const locale = Alpine.store('learningLang')?.code === 'bn' ? 'bn' : 'en';

            window.dispatchEvent(new CustomEvent('assistant:context', {
                detail: {
                    source: 'learning-hub',
                    module_slug: this.courseSlug,
                    lesson_index: this.view === 'course' ? this.lessonIndex : null,
                    locale,
                    role: this.activeRole,
                },
            }));
        },

        init() {
            this.loadProgress();
            this.loadRole();
            this.parseRoute(initialModule);
            this.publishAssistantContext();

            window.addEventListener('popstate', () => this.parseRoute());
            window.addEventListener('learning:lang-changed', () => {
                this.publishAssistantContext();
                this.$nextTick(() => this.renderLessonVisuals());
            });
            window.addEventListener('learning:mermaid-ready', () => {
                this.$nextTick(() => this.renderLessonVisuals());
            });
            window.addEventListener('erp:theme-changed', () => {
                this.$nextTick(() => this.renderLessonVisuals());
            });
        },

        loadProgress() {
            try {
                const raw = localStorage.getItem(progressKey);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed?.courses) {
                        this.progress = parsed;
                        return;
                    }
                }

                const legacy = localStorage.getItem('erp-learning-progress');
                if (legacy) {
                    const slugs = JSON.parse(legacy);
                    if (Array.isArray(slugs)) {
                        slugs.forEach((slug) => {
                            this.progress.courses[slug] = {
                                lessons: [],
                                quizPassed: false,
                                quizScore: 0,
                            };
                        });
                        this.saveProgress();
                    }
                }
            } catch (error) {
                this.progress = { courses: {} };
            }
        },

        saveProgress() {
            try {
                localStorage.setItem(progressKey, JSON.stringify(this.progress));
            } catch (error) {
                // ignore
            }
        },

        loadRole() {
            try {
                const raw = localStorage.getItem(roleKey);
                if (raw && (this.roles ?? []).some((r) => r.slug === raw)) {
                    this.activeRole = raw;
                }
            } catch (error) {
                // ignore
            }
        },

        saveRole() {
            try {
                localStorage.setItem(roleKey, this.activeRole);
            } catch (error) {
                // ignore
            }
        },

        parseRoute(forcedSlug = null) {
            const hash = window.location.hash.replace(/^#/, '');
            let slug = forcedSlug;
            let lesson = 0;

            if (hash.startsWith('course/')) {
                const parts = hash.split('/');
                slug = parts[1] ?? null;
                lesson = parseInt(parts[2] ?? '0', 10) || 0;
            } else if (hash && hash !== 'catalog' && this.modules.some((m) => m.slug === hash)) {
                slug = hash;
                lesson = 0;
            }

            if (slug && this.modules.some((m) => m.slug === slug)) {
                this.openCourse(slug, lesson, false);
                return;
            }

            this.view = 'catalog';
            this.courseSlug = null;
            this.lessonIndex = 0;
        },

        pushRoute() {
            if (this.view === 'catalog') {
                const target = '#catalog';
                if (window.location.hash !== target) {
                    history.pushState(null, '', target);
                }
                return;
            }

            const target = `#course/${this.courseSlug}/${this.lessonIndex}`;
            if (window.location.hash !== target) {
                history.pushState(null, '', target);
            }
        },

        courseData(slug = null) {
            const key = slug ?? this.courseSlug;
            return this.modules.find((m) => m.slug === key) ?? null;
        },

        lessons(slug = null) {
            return this.courseData(slug)?.course?.lessons ?? [];
        },

        currentLesson() {
            return this.lessons()[this.lessonIndex] ?? null;
        },

        courseRecord(slug = null) {
            const key = slug ?? this.courseSlug;
            if (! this.progress.courses[key]) {
                this.progress.courses[key] = {
                    lessons: [],
                    quizPassed: false,
                    quizScore: 0,
                    lastLesson: 0,
                };
            }
            return this.progress.courses[key];
        },

        markLessonComplete(lessonId) {
            if (! lessonId || ! this.courseSlug) {
                return;
            }

            const record = this.courseRecord();
            if (! record.lessons.includes(lessonId)) {
                record.lessons.push(lessonId);
            }
            record.lastLesson = this.lessonIndex;
            this.saveProgress();
        },

        isLessonComplete(slug, lessonId) {
            const record = this.progress.courses[slug];
            return Boolean(record?.lessons?.includes(lessonId));
        },

        courseStatus(slug) {
            const mod = this.courseData(slug);
            if (! mod) {
                return 'not_started';
            }

            const record = this.progress.courses[slug];
            if (record?.quizPassed) {
                return 'completed';
            }

            if (record?.lessons?.length) {
                return 'in_progress';
            }

            return 'not_started';
        },

        courseLessonPercent(slug) {
            const total = this.lessons(slug).length || 1;
            const record = this.progress.courses[slug];
            const done = record?.quizPassed
                ? total
                : Math.min(record?.lessons?.length ?? 0, total - 1);

            return Math.round((done / total) * 100);
        },

        completedCourseCount() {
            return this.modules.filter((m) => this.courseStatus(m.slug) === 'completed').length;
        },

        inProgressCourseCount() {
            return this.modules.filter((m) => this.courseStatus(m.slug) === 'in_progress').length;
        },

        averageQuizScore() {
            const scores = Object.values(this.progress.courses)
                .map((c) => c.quizScore)
                .filter((s) => s > 0);

            if (! scores.length) {
                return null;
            }

            return Math.round(scores.reduce((a, b) => a + b, 0) / scores.length);
        },

        progressPercent() {
            if (! this.modules.length) {
                return 0;
            }

            return Math.round((this.completedCourseCount() / this.modules.length) * 100);
        },

        continueTarget() {
            let best = null;

            this.modules.forEach((mod) => {
                const status = this.courseStatus(mod.slug);
                if (status === 'completed') {
                    return;
                }

                const record = this.progress.courses[mod.slug];
                const idx = record?.lastLesson ?? 0;

                if (status === 'in_progress' || ! best) {
                    best = { slug: mod.slug, lesson: idx, status };
                }
            });

            if (best) {
                return best;
            }

            const pathSlug = this.recommendedPathModules()[0]?.slug;
            if (pathSlug) {
                return { slug: pathSlug, lesson: 0, status: 'not_started' };
            }

            return this.modules[0]
                ? { slug: this.modules[0].slug, lesson: 0, status: 'not_started' }
                : null;
        },

        setRole(slug) {
            this.activeRole = slug;
            this.saveRole();
            this.publishAssistantContext();
        },

        roleMatches(mod) {
            if (this.activeRole === 'all') {
                return true;
            }

            const modRoles = mod?.roles ?? ['all'];

            if (modRoles.includes('all')) {
                return true;
            }

            return modRoles.includes(this.activeRole);
        },

        filteredModules() {
            const q = this.search.trim().toLowerCase();

            return this.modules.filter((mod) => {
                if (! this.roleMatches(mod)) {
                    return false;
                }

                if (! q) {
                    return true;
                }

                const title = this.t(mod.title).toLowerCase();
                const summary = this.t(mod.summary ?? { en: '', bn: '' }).toLowerCase();
                const track = this.t(mod.course?.meta?.track ?? { en: '', bn: '' }).toLowerCase();
                return title.includes(q) || summary.includes(q) || mod.slug.includes(q) || track.includes(q);
            });
        },

        recommendedPathModules() {
            const slugs = this.learningPath.length
                ? this.learningPath
                : this.modules.map((m) => m.slug);

            return slugs
                .map((slug) => this.modules.find((m) => m.slug === slug))
                .filter((m) => m && this.roleMatches(m));
        },

        t(pair) {
            return Alpine.store('learningLang').pick(pair);
        },

        formatProse(text) {
            if (! text) {
                return '';
            }

            const escaped = String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            return escaped
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/`([^`]+)`/g, '<code class="learning-prose__code">$1</code>')
                .replace(/\n\n/g, '</p><p class="learning-prose__p">')
                .replace(/\n/g, '<br>');
        },

        quizIntro() {
            const pass = this.courseData()?.course?.pass_percent ?? 70;
            return this.t(this.ui.quiz_intro).replace('%pass%', String(pass));
        },

        goCatalog() {
            this.view = 'catalog';
            this.courseSlug = null;
            this.lessonIndex = 0;
            this.resetQuizState();
            this.pushRoute();
            this.publishAssistantContext();
        },

        openCourse(slug, lessonIndex = 0, push = true) {
            const mod = this.courseData(slug);
            if (! mod) {
                return;
            }

            const max = Math.max(0, this.lessons(slug).length - 1);
            this.view = 'course';
            this.courseSlug = slug;
            this.lessonIndex = Math.min(Math.max(0, lessonIndex), max);
            this.openFaq = null;
            this.resetQuizState();

            if (push) {
                this.pushRoute();
            }

            this.$nextTick(() => {
                this.renderLessonVisuals();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            this.publishAssistantContext();
        },

        goLesson(index) {
            const max = this.lessons().length - 1;
            this.lessonIndex = Math.min(Math.max(0, index), max);
            this.openFaq = null;
            this.glossarySearch = '';
            this.resetQuizState();
            this.pushRoute();
            this.$nextTick(() => this.renderLessonVisuals());
            this.publishAssistantContext();
        },

        nextLesson() {
            const lesson = this.currentLesson();
            if (lesson?.id && lesson.type !== 'quiz') {
                this.markLessonComplete(lesson.id);
            }

            if (this.lessonIndex < this.lessons().length - 1) {
                this.goLesson(this.lessonIndex + 1);
            }
        },

        prevLesson() {
            if (this.lessonIndex > 0) {
                this.goLesson(this.lessonIndex - 1);
            }
        },

        completeAndNext() {
            const lesson = this.currentLesson();
            if (lesson?.id) {
                this.markLessonComplete(lesson.id);
            }
            this.nextLesson();
        },

        resetQuizState() {
            this.quizAnswers = {};
            this.quizSubmitted = false;
            this.quizScore = 0;
            this.quizPassed = false;
        },

        quizQuestions() {
            return this.courseData()?.quiz?.questions ?? [];
        },

        setQuizAnswer(questionId, optionId) {
            if (this.quizSubmitted) {
                return;
            }
            this.quizAnswers[questionId] = optionId;
        },

        submitQuiz() {
            const questions = this.quizQuestions();
            if (! questions.length) {
                return;
            }

            let correct = 0;
            questions.forEach((q) => {
                if (this.quizAnswers[q.id] === q.correct) {
                    correct += 1;
                }
            });

            const passPercent = this.courseData()?.course?.pass_percent ?? 70;
            this.quizScore = Math.round((correct / questions.length) * 100);
            this.quizPassed = this.quizScore >= passPercent;
            this.quizSubmitted = true;

            const record = this.courseRecord();
            record.quizScore = this.quizScore;
            record.quizPassed = this.quizPassed;
            if (this.quizPassed) {
                this.markLessonComplete('quiz');
            }
            this.saveProgress();
        },

        retryQuiz() {
            this.resetQuizState();
        },

        filteredGlossary() {
            const terms = this.currentLesson()?.terms ?? [];
            const q = this.glossarySearch.trim().toLowerCase();

            if (! q) {
                return terms;
            }

            return terms.filter((item) => {
                const term = this.t(item.term).toLowerCase();
                const def = this.t(item.def).toLowerCase();
                return term.includes(q) || def.includes(q);
            });
        },

        hasTechnical() {
            return Boolean(this.courseData()?.deep_sections?.length);
        },

        toggleFaq(index) {
            this.openFaq = this.openFaq === index ? null : index;
        },

        printGuide() {
            window.print();
        },

        trackClass(mod) {
            const track = this.t(mod?.course?.meta?.track ?? { en: '', bn: '' }).toLowerCase();
            if (track.includes('foundation') || track.includes('ভিত্তি')) {
                return 'track-foundation';
            }
            if (track.includes('operation') || track.includes('অপারেশন')) {
                return 'track-operations';
            }
            if (track.includes('commercial') || track.includes('বাণিজ্য')) {
                return 'track-commercial';
            }
            if (track.includes('finance') || track.includes('অর্থ')) {
                return 'track-finance';
            }
            return 'track-general';
        },

        iconClass(mod) {
            return `icon-${mod?.icon ?? 'overview'}`;
        },

        async renderMermaidInto(el, source) {
            if (! el || ! source) {
                if (el) {
                    el.innerHTML = '';
                }
                return;
            }

            if (typeof mermaid === 'undefined') {
                el.innerHTML = '<p class="learning-course__flow-loading">Loading diagram…</p>';
                return;
            }

            el.innerHTML = '<p class="learning-course__flow-loading">Drawing flow…</p>';
            const id = `learning-flow-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;

            try {
                const { svg } = await mermaid.render(id, source);
                el.innerHTML = svg;
            } catch (error) {
                el.innerHTML = '<div class="learning-course__flow-error"><p>Could not draw diagram.</p></div>';
            }
        },

        async renderLessonVisuals() {
            if (this.view !== 'course') {
                return;
            }

            const lesson = this.currentLesson();
            const refEl = document.querySelector('.learning-course__panel--reference .learning-course__flow-chart');

            if (refEl && lesson?.type === 'reference') {
                const mod = this.courseData();
                const source = mod?.flowchart_technical
                    ? normalizeMermaidSource(Alpine.store('learningLang').pick(mod.flowchart_technical))
                    : '';
                await this.renderMermaidInto(refEl, source);
            }

            const diagramBlocks = (lesson?.blocks ?? []).filter((block) => block?.type === 'diagram');
            const diagramEls = document.querySelectorAll('[data-learning-diagram]');

            for (let i = 0; i < diagramEls.length; i += 1) {
                const block = diagramBlocks[i];
                const source = block?.source
                    ? normalizeMermaidSource(Alpine.store('learningLang').pick(block.source))
                    : '';
                await this.renderMermaidInto(diagramEls[i], source);
            }
        },
    };
}
