{{-- Rich content blocks for course lessons (Alpine parent scope) --}}
<div class="learning-blocks">
    <template x-for="(block, bIndex) in (currentLesson()?.blocks ?? [])" :key="bIndex">
        <div class="learning-block" :class="'learning-block--' + (block.type ?? '')">

            <template x-if="block.type === 'prose'">
                <div class="learning-block__prose learning-prose" x-html="'<p class=\'learning-prose__p\'>' + formatProse(t(block.body)) + '</p>'"></div>
            </template>

            <template x-if="block.type === 'callout'">
                <div class="learning-block__callout" :class="'is-' + (block.variant ?? 'info')">
                    <template x-if="block.title">
                        <h4 class="learning-block__callout-title" x-text="t(block.title)"></h4>
                    </template>
                    <p x-html="formatProse(t(block.body))"></p>
                    <template x-if="block.path">
                        <a :href="block.path" class="learning-academy__btn learning-academy__btn--primary learning-block__cta" x-text="t(ui.open_screen)"></a>
                    </template>
                </div>
            </template>

            <template x-if="block.type === 'checklist'">
                <div class="learning-block__checklist-wrap">
                    <h4 class="learning-block__title" x-text="t(block.title ?? { en: 'Checklist', bn: 'চেকলিস্ট' })"></h4>
                    <ul class="learning-block__checklist">
                        <template x-for="(item, cIndex) in (block.items ?? [])" :key="cIndex">
                            <li x-html="formatProse(t(item))"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="block.type === 'timeline'">
                <div class="learning-block__timeline">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <ol class="learning-block__timeline-list">
                        <template x-for="(item, tIndex) in (block.items ?? [])" :key="tIndex">
                            <li x-html="formatProse(t(item))"></li>
                        </template>
                    </ol>
                </div>
            </template>

            <template x-if="block.type === 'module_grid'">
                <div class="learning-block__module-grid">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <div class="learning-block__module-rows">
                        <template x-for="(row, mIndex) in (block.rows ?? [])" :key="mIndex">
                            <div class="learning-block__module-row">
                                <span class="learning-block__module-num" x-text="row.num"></span>
                                <div>
                                    <strong x-text="t(row.name)"></strong>
                                    <span x-text="t(row.output)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="block.type === 'screen'">
                <div class="learning-mock">
                    <div class="learning-mock__chrome">
                        <span class="learning-mock__dot"></span>
                        <span class="learning-mock__dot"></span>
                        <span class="learning-mock__dot"></span>
                        <span class="learning-mock__url" x-text="block.path ?? ''"></span>
                    </div>
                    <div class="learning-mock__body">
                        <div class="learning-mock__sidebar">
                            <span></span><span></span><span></span><span class="is-active"></span><span></span>
                        </div>
                        <div class="learning-mock__main">
                            <p class="learning-mock__breadcrumb" x-text="t(block.menu)"></p>
                            <h4 class="learning-mock__screen-title" x-text="t(block.title)"></h4>

                            <template x-if="(block.highlights ?? []).length">
                                <div class="learning-mock__highlights">
                                    <template x-for="(hi, hIndex) in (block.highlights ?? [])" :key="hIndex">
                                        <div class="learning-mock__highlight" :class="{ 'is-pulse': hIndex === 0 }">
                                            <strong x-text="t(hi.label)"></strong>
                                            <span x-text="t(hi.desc)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="(block.form ?? []).length">
                                <div class="learning-mock__form">
                                    <template x-for="(field, fIndex) in (block.form ?? [])" :key="fIndex">
                                        <div class="learning-mock__field" :class="{ 'is-required': field.required }">
                                            <label>
                                                <span x-text="field.field"></span>
                                                <template x-if="field.required"><em x-text="t(ui.field_required)"></em></template>
                                            </label>
                                            <div class="learning-mock__input" x-text="field.example"></div>
                                            <p class="learning-mock__hint" x-text="t(field.hint)"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="(block.table ?? []).length">
                                <table class="learning-mock__table">
                                    <thead>
                                        <tr>
                                            <template x-for="(col, tcIndex) in (block.table ?? [])" :key="tcIndex">
                                                <th x-text="col.col"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <template x-for="(col, tcIndex) in (block.table ?? [])" :key="'s'+tcIndex">
                                                <td x-text="col.sample"></td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>

                            <template x-if="block.path">
                                <a :href="block.path" class="learning-mock__open-btn" x-text="t(ui.open_live_screen)"></a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="block.type === 'clicks'">
                <div class="learning-block__clicks">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <ol class="learning-block__click-list">
                        <template x-for="(item, clIndex) in (block.items ?? [])" :key="clIndex">
                            <li>
                                <span class="learning-block__click-num" x-text="clIndex + 1"></span>
                                <span x-html="formatProse(t(item))"></span>
                            </li>
                        </template>
                    </ol>
                </div>
            </template>

            <template x-if="block.type === 'status_table'">
                <div class="learning-block__status-table">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <table>
                        <thead><tr><th x-text="t(ui.status_col)"></th><th x-text="t(ui.meaning_col)"></th></tr></thead>
                        <tbody>
                            <template x-for="(row, stIndex) in (block.rows ?? [])" :key="stIndex">
                                <tr>
                                    <td><span class="learning-block__pill" x-text="row.status"></span></td>
                                    <td x-text="t(row.meaning)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <template x-if="block.type === 'compare'">
                <div class="learning-block__compare">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <div class="learning-block__compare-grid">
                        <template x-for="(col, cmpIndex) in (block.columns ?? [])" :key="cmpIndex">
                            <div class="learning-block__compare-col">
                                <h5 x-text="t(col.header)"></h5>
                                <ul>
                                    <template x-for="(row, crIndex) in (col.rows ?? [])" :key="crIndex">
                                        <li x-text="t(row)"></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="block.type === 'pipeline'">
                <div class="learning-pipeline">
                    <template x-if="block.title">
                        <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    </template>
                    <div class="learning-pipeline__track">
                        <template x-for="(step, pIndex) in (block.steps ?? [])" :key="pIndex">
                            <div class="learning-pipeline__item">
                                <div class="learning-pipeline__card"
                                     :class="'phase-' + (step.phase ?? 'operations')">
                                    <span class="learning-pipeline__num" x-text="pIndex + 1"></span>
                                    <strong class="learning-pipeline__label" x-text="t(step.label)"></strong>
                                    <p class="learning-pipeline__desc" x-text="t(step.desc)"></p>
                                    <template x-if="step.path">
                                        <a :href="step.path" class="learning-pipeline__link" x-text="t(ui.open_screen)"></a>
                                    </template>
                                </div>
                                <template x-if="pIndex < (block.steps ?? []).length - 1">
                                    <div class="learning-pipeline__arrow" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="block.type === 'diagram'">
                <div class="learning-block__diagram-wrap">
                    <h4 class="learning-block__title" x-show="block.title" x-text="block.title ? t(block.title) : ''"></h4>
                    <div class="learning-block__diagram" data-learning-diagram></div>
                </div>
            </template>

            <template x-if="block.type === 'example_card'">
                <article class="learning-block__example">
                    <h4 x-text="t(block.title)"></h4>
                    <div x-html="formatProse(t(block.body))"></div>
                </article>
            </template>

            <template x-if="block.type === 'tips'">
                <div class="learning-block__tips-wrap">
                    <h4 class="learning-block__title" x-text="t(block.title)"></h4>
                    <ul class="learning-block__tips">
                        <template x-for="(tip, tipIndex) in (block.items ?? [])" :key="tipIndex">
                            <li x-html="formatProse(t(tip))"></li>
                        </template>
                    </ul>
                </div>
            </template>

        </div>
    </template>
</div>
