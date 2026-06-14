@extends('layouts.app')

@push('styles')
    @vite(['resources/css/learning-hub.css'])
@endpush

@section('content')
<div
    class="learning-academy screen-learning-hub"
    x-data="learningHub(@js($modules), @js($ui), @js($roles), @js($initialModule), @js($defaultRole), @js($learningPath))"
>
    {{-- ═══ CATALOG HOME ═══ --}}
    <div class="learning-academy__catalog" x-show="view === 'catalog'" x-cloak>
        <header class="learning-academy__hero">
            <div class="learning-academy__hero-main">
                <p class="learning-academy__eyebrow" x-text="t(ui.overview_badge)"></p>
                <h1 class="learning-academy__title" x-text="t(ui.hub_title)"></h1>
                <p class="learning-academy__subtitle" x-text="t(ui.hub_subtitle)"></p>
            </div>
            <div class="learning-academy__hero-stats">
                <div class="learning-academy__stat">
                    <span class="learning-academy__stat-value" x-text="completedCourseCount()"></span>
                    <span class="learning-academy__stat-label" x-text="t(ui.stat_completed)"></span>
                </div>
                <div class="learning-academy__stat">
                    <span class="learning-academy__stat-value" x-text="inProgressCourseCount()"></span>
                    <span class="learning-academy__stat-label" x-text="t(ui.stat_in_progress)"></span>
                </div>
                <div class="learning-academy__stat">
                    <span class="learning-academy__stat-value" x-text="averageQuizScore() !== null ? averageQuizScore() + '%' : '—'"></span>
                    <span class="learning-academy__stat-label" x-text="t(ui.stat_avg_score)"></span>
                </div>
            </div>
        </header>

        <template x-if="continueTarget()">
            <section class="learning-academy__continue learning-academy__continue--no-print">
                <div class="learning-academy__continue-body">
                    <p class="learning-academy__continue-label" x-text="t(ui.continue_learning)"></p>
                    <h2 class="learning-academy__continue-title" x-text="t(courseData(continueTarget().slug)?.title ?? { en: '', bn: '' })"></h2>
                    <p class="learning-academy__continue-meta">
                        <span x-text="t(ui.lesson_of)"></span>
                        <span x-text="(continueTarget().lesson + 1)"></span>
                        <span x-text="t(ui.of)"></span>
                        <span x-text="lessons(continueTarget().slug).length"></span>
                    </p>
                </div>
                <button type="button"
                        class="learning-academy__btn learning-academy__btn--primary"
                        @click="openCourse(continueTarget().slug, continueTarget().lesson)"
                        x-text="t(ui.continue_course)">
                </button>
            </section>
        </template>

        <div class="learning-academy__toolbar learning-academy__toolbar--no-print">
            <div class="learning-academy__role-block">
                <span class="learning-academy__toolbar-label" x-text="t(ui.role_filter)"></span>
                <div class="learning-academy__roles">
                    <template x-for="role in roles" :key="role.slug">
                        <button type="button"
                                class="learning-academy__role"
                                :class="{ 'is-active': activeRole === role.slug }"
                                @click="setRole(role.slug)"
                                x-text="t(role.label)">
                        </button>
                    </template>
                </div>
            </div>
            <div class="learning-academy__search-wrap">
                <svg class="learning-academy__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search" class="learning-academy__search" x-model="search" :placeholder="t(ui.search_placeholder)">
            </div>
        </div>

        <section class="learning-academy__path learning-academy__path--no-print" x-show="recommendedPathModules().length > 1">
            <div class="learning-academy__path-head">
                <h2 class="learning-academy__section-title" x-text="t(ui.recommended_path)"></h2>
                <p class="learning-academy__path-hint" x-text="t(ui.path_hint)"></p>
            </div>
            <div class="learning-academy__path-track">
                <template x-for="(mod, pIndex) in recommendedPathModules()" :key="mod.slug">
                    <button type="button"
                            class="learning-academy__path-chip"
                            :class="{
                                'is-done': courseStatus(mod.slug) === 'completed',
                                'is-active': courseStatus(mod.slug) === 'in_progress',
                            }"
                            @click="openCourse(mod.slug, 0)">
                        <span class="learning-academy__path-num" x-text="pIndex + 1"></span>
                        <span x-text="t(mod.title)"></span>
                    </button>
                </template>
            </div>
        </section>

        <section class="learning-academy__catalog-section">
            <div class="learning-academy__catalog-head">
                <h2 class="learning-academy__section-title" x-text="t(ui.courses_title)"></h2>
                <span class="learning-academy__catalog-count">
                    <span x-text="filteredModules().length"></span>
                    <span x-text="t(ui.courses_count)"></span>
                </span>
            </div>

            <p class="learning-academy__empty" x-show="filteredModules().length === 0" x-text="t(ui.no_results)"></p>

            <div class="learning-academy__grid">
                <template x-for="mod in filteredModules()" :key="mod.slug">
                    <article class="learning-course-card"
                             :class="[trackClass(mod), { 'is-completed': courseStatus(mod.slug) === 'completed' }]">
                        <div class="learning-course-card__top">
                            <div class="learning-course-card__icon" :class="iconClass(mod)">
                                <span x-text="String(mod.order ?? 0).padStart(2, '0')"></span>
                            </div>
                            <div class="learning-course-card__badges">
                                <span class="learning-course-card__track" x-text="t(mod.course?.meta?.track)"></span>
                                <span class="learning-course-card__level" x-text="t(mod.course?.meta?.level)"></span>
                            </div>
                        </div>
                        <h3 class="learning-course-card__title" x-text="t(mod.title)"></h3>
                        <p class="learning-course-card__summary" x-text="t(mod.summary ?? { en: '', bn: '' })"></p>
                        <div class="learning-course-card__meta">
                            <span>
                                <span x-text="mod.course?.lesson_count ?? 0"></span>
                                <span x-text="t(ui.lessons_label)"></span>
                            </span>
                            <span>·</span>
                            <span>
                                <span x-text="mod.course?.duration_min ?? 10"></span>
                                <span x-text="t(ui.minutes_label)"></span>
                            </span>
                            <template x-if="mod.course?.quiz_question_count">
                                <span>· <span x-text="t(ui.has_quiz)"></span></span>
                            </template>
                        </div>
                        <div class="learning-course-card__progress">
                            <div class="learning-course-card__progress-track">
                                <div class="learning-course-card__progress-fill"
                                     :style="`width: ${courseLessonPercent(mod.slug)}%`"></div>
                            </div>
                            <span class="learning-course-card__status"
                                  x-text="courseStatus(mod.slug) === 'completed' ? t(ui.completed) : (courseStatus(mod.slug) === 'in_progress' ? t(ui.in_progress) : t(ui.not_started))">
                            </span>
                        </div>
                        <button type="button"
                                class="learning-course-card__cta"
                                @click="openCourse(mod.slug, courseStatus(mod.slug) === 'in_progress' ? (progress.courses[mod.slug]?.lastLesson ?? 0) : 0)">
                            <span x-text="courseStatus(mod.slug) === 'completed' ? t(ui.review_course) : (courseStatus(mod.slug) === 'in_progress' ? t(ui.continue_course) : t(ui.start_course))"></span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </article>
                </template>
            </div>
        </section>

        <footer class="learning-academy__footer learning-academy__footer--no-print">
            <div class="learning-academy__progress-global">
                <div class="learning-academy__progress-label">
                    <span x-text="t(ui.progress_label)"></span>
                    <span><span x-text="completedCourseCount()"></span>/<span x-text="modules.length"></span> <span x-text="t(ui.progress_of)"></span></span>
                </div>
                <div class="learning-academy__progress-track">
                    <div class="learning-academy__progress-fill" :style="`width: ${progressPercent()}%`"></div>
                </div>
            </div>
            <div class="learning-academy__footer-actions">
                @if(Route::has('admin.client-guide'))
                    <a href="{{ route('admin.client-guide') }}" class="learning-academy__btn learning-academy__btn--ghost" x-text="t(ui.full_manual)"></a>
                @endif
                <button type="button" class="learning-academy__btn learning-academy__btn--ghost" @click="$store.tour.start(); $store.tour.closeLauncher()" x-text="t(ui.start_tour)"></button>
            </div>
        </footer>
    </div>

    {{-- ═══ COURSE PLAYER ═══ --}}
    <div class="learning-course" x-show="view === 'course'" x-cloak>
        <template x-if="courseData()">
            <div class="learning-course__layout">
                <aside class="learning-course__sidebar learning-course__sidebar--no-print">
                    <button type="button" class="learning-course__back" @click="goCatalog()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span x-text="t(ui.back_catalog)"></span>
                    </button>
                    <p class="learning-course__sidebar-label" x-text="t(ui.course_outline)"></p>
                    <h2 class="learning-course__sidebar-title" x-text="t(courseData().title)"></h2>
                    <nav class="learning-course__nav">
                        <template x-for="(lesson, lIndex) in lessons()" :key="lesson.id">
                            <button type="button"
                                    class="learning-course__nav-item"
                                    :class="{
                                        'is-current': lessonIndex === lIndex,
                                        'is-done': isLessonComplete(courseSlug, lesson.id) || (lesson.type === 'quiz' && progress.courses[courseSlug]?.quizPassed),
                                    }"
                                    @click="goLesson(lIndex)">
                                <span class="learning-course__nav-num" x-text="lIndex + 1"></span>
                                <span class="learning-course__nav-label" x-text="t(lesson.title)"></span>
                            </button>
                        </template>
                    </nav>
                </aside>

                <main class="learning-course__main">
                    <header class="learning-course__header">
                        <div>
                            <p class="learning-course__lesson-meta">
                                <span x-text="t(ui.lesson_of)"></span>
                                <span x-text="lessonIndex + 1"></span>
                                <span x-text="t(ui.of)"></span>
                                <span x-text="lessons().length"></span>
                                <span>·</span>
                                <span x-text="t(currentLesson()?.title ?? { en: '', bn: '' })"></span>
                            </p>
                            <h1 class="learning-course__lesson-title" x-text="t(currentLesson()?.title ?? courseData().title)"></h1>
                        </div>
                        <button type="button" class="learning-academy__btn learning-academy__btn--ghost learning-course__print--no-print" @click="printGuide()" x-text="t(ui.print_guide)"></button>
                    </header>

                    {{-- Welcome / Flow / Step / Practice — rich blocks --}}
                    <template x-if="['welcome','flow','step','practice'].includes(currentLesson()?.type)">
                        <div class="learning-course__panel"
                             :class="{
                                 'learning-course__panel--step': currentLesson()?.type === 'step',
                                 'learning-course__panel--welcome': currentLesson()?.type === 'welcome',
                             }">
                            <template x-if="currentLesson()?.type === 'step'">
                                <div class="learning-course__step-header">
                                    <div class="learning-course__step-badge" x-text="currentLesson().step_number"></div>
                                    <template x-if="currentLesson().path">
                                        <a :href="currentLesson().path" class="learning-academy__btn learning-academy__btn--primary" x-text="t(ui.open_screen)"></a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="currentLesson()?.type === 'welcome'">
                                <p class="learning-course__lead" x-text="t(courseData().summary ?? { en: '', bn: '' })"></p>
                                <template x-if="courseData().audience">
                                    <p class="learning-course__audience">
                                        <strong x-text="t(ui.audience_label)"></strong>
                                        <span x-text="t(courseData().audience)"></span>
                                    </p>
                                </template>
                                <section class="learning-course__outcomes" x-show="(courseData().course?.meta?.outcomes ?? []).length">
                                    <h3 x-text="t(ui.what_you_learn)"></h3>
                                    <ul>
                                        <template x-for="(outcome, oIndex) in (courseData().course?.meta?.outcomes ?? [])" :key="oIndex">
                                            <li x-text="t(outcome)"></li>
                                        </template>
                                    </ul>
                                </section>
                            </template>

                            <template x-if="currentLesson()?.type === 'flow'">
                                <h3 class="learning-course__panel-title" x-text="t(ui.flow_title)"></h3>
                            </template>

                            @include('admin.learning.partials.lesson-blocks')

                            <template x-if="currentLesson()?.type === 'welcome' && (courseData().related_screens ?? []).length">
                                <section class="learning-course__related">
                                    <h3 x-text="t(ui.related_screens)"></h3>
                                    <div class="learning-course__related-grid">
                                        <template x-for="(screen, sIndex) in (courseData().related_screens ?? [])" :key="sIndex">
                                            <a :href="screen.path" class="learning-course__related-chip" x-text="t(screen.title)"></a>
                                        </template>
                                    </div>
                                </section>
                            </template>
                        </div>
                    </template>

                    {{-- Reference --}}
                    <template x-if="currentLesson()?.type === 'reference'">
                        <div class="learning-course__panel learning-course__panel--reference">
                            <p class="learning-course__reference-intro" x-text="t(ui.expert_reference)"></p>
                            <div class="learning-course__flow-chart" x-show="courseData().flowchart_technical"></div>

                            <template x-for="(section, sIndex) in (courseData().deep_sections ?? [])" :key="section.id ?? sIndex">
                                <article class="learning-course__ref-card">
                                    <h3 x-text="t(section.title)"></h3>
                                    <template x-if="section.type === 'prose'">
                                        <div class="learning-prose" x-html="'<p class=\'learning-prose__p\'>' + formatProse(t(section.body)) + '</p>'"></div>
                                    </template>
                                    <template x-if="section.intro">
                                        <p class="learning-course__intro" x-text="t(section.intro)"></p>
                                    </template>
                                    <template x-if="section.type === 'scenario'">
                                        <div class="learning-course__scenario">
                                            <template x-for="(row, scIndex) in (section.rows ?? [])" :key="scIndex">
                                                <div class="learning-course__scenario-row"
                                                     :class="{
                                                         'is-profit': row.role === 'profit',
                                                         'is-cost': row.role === 'cost',
                                                         'is-revenue': row.role === 'revenue',
                                                         'is-vat': row.role === 'vat',
                                                     }">
                                                    <span x-text="t(row.label)"></span>
                                                    <span x-text="t(row.value)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="section.type === 'journal'">
                                        <table class="learning-course__table">
                                            <thead><tr><th x-text="t(ui.account)"></th><th x-text="t(ui.debit)"></th><th x-text="t(ui.credit)"></th></tr></thead>
                                            <tbody>
                                                <template x-for="(row, rIndex) in (section.rows ?? [])" :key="rIndex">
                                                    <tr>
                                                        <td x-text="t(row.account)"></td>
                                                        <td class="is-debit" x-text="t(row.debit)"></td>
                                                        <td class="is-credit" x-text="t(row.credit)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                    <template x-if="section.type === 'coa_table'">
                                        <table class="learning-course__table">
                                            <thead><tr><th x-text="t(ui.code)"></th><th x-text="t(ui.account)"></th><th x-text="t(ui.type)"></th></tr></thead>
                                            <tbody>
                                                <template x-for="(row, rIndex) in (section.rows ?? [])" :key="rIndex">
                                                    <tr>
                                                        <td x-text="row.code ?? ''"></td>
                                                        <td x-text="t(row.name)"></td>
                                                        <td x-text="row.type ?? ''"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                    <template x-if="section.type === 'mapping_table'">
                                        <table class="learning-course__table learning-course__table--mapping">
                                            <thead>
                                                <tr>
                                                    <th x-text="t(ui.mapping_action ?? { en: 'Business action', bn: 'ব্যবসায়িক কাজ' })"></th>
                                                    <th x-text="t(ui.mapping_when ?? { en: 'When it posts', bn: 'কখন পোস্ট' })"></th>
                                                    <th x-text="t(ui.debit)"></th>
                                                    <th x-text="t(ui.credit)"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(row, rIndex) in (section.rows ?? [])" :key="rIndex">
                                                    <tr>
                                                        <td x-text="t(row.action)"></td>
                                                        <td x-text="t(row.when)"></td>
                                                        <td class="is-debit" x-text="t(row.debit)"></td>
                                                        <td class="is-credit" x-text="t(row.credit)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                    <template x-if="section.type === 'formula'">
                                        <template x-for="(item, fIndex) in (section.items ?? [])" :key="fIndex">
                                            <div class="learning-course__formula">
                                                <strong x-text="t(item.label)"></strong>
                                                <span x-text="t(item.formula)"></span>
                                            </div>
                                        </template>
                                    </template>
                                    <template x-if="section.type === 'checklist'">
                                        <ul class="learning-course__checklist">
                                            <template x-for="(item, cIndex) in (section.items ?? [])" :key="cIndex">
                                                <li x-html="formatProse(t(item))"></li>
                                            </template>
                                        </ul>
                                    </template>
                                    <template x-if="section.note">
                                        <p class="learning-course__note" x-html="formatProse(t(section.note))"></p>
                                    </template>
                                </article>
                            </template>

                            <section x-show="(courseData().faqs ?? []).length">
                                <h3 x-text="t(ui.faq_title)"></h3>
                                <div class="learning-course__faq-list">
                                    <template x-for="(faq, fIndex) in (courseData().faqs ?? [])" :key="fIndex">
                                        <div class="learning-course__faq" :class="{ 'is-open': openFaq === fIndex }">
                                            <button type="button" @click="toggleFaq(fIndex)" x-text="t(faq.q)"></button>
                                            <div x-show="openFaq === fIndex" x-cloak><p x-html="formatProse(t(faq.a))"></p></div>
                                        </div>
                                    </template>
                                </div>
                            </section>

                            <section x-show="(courseData().report_links ?? []).length">
                                <h3 x-text="t(ui.reports_title)"></h3>
                                <div class="learning-course__report-grid">
                                    <template x-for="(report, rIndex) in (courseData().report_links ?? [])" :key="rIndex">
                                        <a :href="report.path" class="learning-course__report-card">
                                            <strong x-text="t(report.title)"></strong>
                                            <span x-text="t(report.desc)"></span>
                                        </a>
                                    </template>
                                </div>
                            </section>
                        </div>
                    </template>

                    {{-- Glossary --}}
                    <template x-if="currentLesson()?.type === 'glossary'">
                        <div class="learning-course__panel learning-course__panel--glossary">
                            <p class="learning-glossary__intro" x-text="t(ui.glossary_intro)"></p>
                            <div class="learning-glossary__search-wrap learning-course__footer--no-print">
                                <input type="search"
                                       class="learning-glossary__search"
                                       x-model="glossarySearch"
                                       :placeholder="t(ui.glossary_search)">
                            </div>
                            <dl class="learning-glossary">
                                <template x-for="(item, gIndex) in filteredGlossary()" :key="gIndex">
                                    <div class="learning-glossary__row">
                                        <dt x-text="t(item.term)"></dt>
                                        <dd x-text="t(item.def)"></dd>
                                    </div>
                                </template>
                            </dl>
                            <p class="learning-glossary__empty" x-show="filteredGlossary().length === 0" x-cloak x-text="t(ui.glossary_empty)"></p>
                        </div>
                    </template>

                    {{-- Quiz --}}
                    <template x-if="currentLesson()?.type === 'quiz'">
                        <div class="learning-course__panel learning-course__panel--quiz">
                            <template x-if="!quizSubmitted">
                                <p class="learning-course__quiz-intro" x-text="quizIntro()"></p>
                                <div class="learning-course__quiz-list">
                                    <template x-for="(question, qIndex) in quizQuestions()" :key="question.id">
                                        <fieldset class="learning-course__quiz-q">
                                            <legend>
                                                <span x-text="(qIndex + 1) + '.'"></span>
                                                <span x-text="t(question.prompt)"></span>
                                            </legend>
                                            <template x-for="option in question.options" :key="option.id">
                                                <label class="learning-course__quiz-opt"
                                                       :class="{ 'is-selected': quizAnswers[question.id] === option.id }">
                                                    <input type="radio"
                                                           :name="'quiz-' + question.id"
                                                           :value="option.id"
                                                           @change="setQuizAnswer(question.id, option.id)"
                                                           :checked="quizAnswers[question.id] === option.id">
                                                    <span x-text="t(option.text)"></span>
                                                </label>
                                            </template>
                                        </fieldset>
                                    </template>
                                </div>
                                <button type="button"
                                        class="learning-academy__btn learning-academy__btn--primary"
                                        @click="submitQuiz()"
                                        :disabled="Object.keys(quizAnswers).length < quizQuestions().length"
                                        x-text="t(ui.quiz_submit)">
                                </button>
                            </template>

                            <template x-if="quizSubmitted">
                                <div class="learning-course__quiz-result" :class="quizPassed ? 'is-pass' : 'is-fail'">
                                    <h3 x-text="quizPassed ? t(ui.quiz_passed) : t(ui.quiz_failed)"></h3>
                                    <p class="learning-course__quiz-score">
                                        <span x-text="t(ui.quiz_score)"></span>:
                                        <strong x-text="quizScore + '%'"></strong>
                                    </p>
                                    <template x-if="quizPassed">
                                        <p class="learning-course__certificate" x-text="t(ui.certificate_body)"></p>
                                    </template>
                                </div>
                                <div class="learning-course__quiz-review">
                                    <template x-for="(question, qIndex) in quizQuestions()" :key="'r-' + question.id">
                                        <div class="learning-course__quiz-review-item"
                                             :class="quizAnswers[question.id] === question.correct ? 'is-correct' : 'is-wrong'">
                                            <p><strong x-text="t(question.prompt)"></strong></p>
                                            <p>
                                                <span x-text="t(ui.quiz_explanation)"></span>:
                                                <span x-text="t(question.explain)"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                                <div class="learning-course__quiz-actions">
                                    <template x-if="!quizPassed">
                                        <button type="button" class="learning-academy__btn learning-academy__btn--primary" @click="retryQuiz()" x-text="t(ui.quiz_retry)"></button>
                                    </template>
                                    <template x-if="quizPassed">
                                        <button type="button" class="learning-academy__btn learning-academy__btn--primary" @click="goCatalog()" x-text="t(ui.back_catalog)"></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <footer class="learning-course__footer learning-course__footer--no-print" x-show="currentLesson()?.type !== 'quiz' || !quizSubmitted">
                        <button type="button"
                                class="learning-academy__btn learning-academy__btn--ghost"
                                @click="prevLesson()"
                                :disabled="lessonIndex === 0"
                                x-text="t(ui.prev_lesson)">
                        </button>
                        <button type="button"
                                class="learning-academy__btn learning-academy__btn--primary"
                                @click="completeAndNext()"
                                x-show="lessonIndex < lessons().length - 1"
                                x-text="t(ui.complete_lesson)">
                        </button>
                        <button type="button"
                                class="learning-academy__btn learning-academy__btn--primary"
                                @click="goLesson(lessons().length - 1)"
                                x-show="lessonIndex === lessons().length - 2 && currentLesson()?.type !== 'quiz'"
                                x-text="t(ui.go_to_quiz)">
                        </button>
                    </footer>
                </main>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
    (function () {
        function initMermaid() {
            if (typeof mermaid === 'undefined') return;
            mermaid.initialize({
                startOnLoad: false,
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
                securityLevel: 'loose',
                fontFamily: window.erpUiFontStack || 'sans-serif',
                flowchart: { htmlLabels: true, curve: 'basis', padding: 18 },
            });
            window.dispatchEvent(new CustomEvent('learning:mermaid-ready'));
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMermaid);
        } else {
            initMermaid();
        }
    })();
</script>
@endpush
