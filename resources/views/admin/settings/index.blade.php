@extends('layouts.app')

@section('content')
<div x-data="{
        primary: '{{ old('brand_primary_color', $settings['brand_primary_color']) }}',
        secondary: '{{ old('brand_secondary_color', $settings['brand_secondary_color']) }}'
    }"
    class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Application settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage branding, currency, communication gateways, and database backups from one place.
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <span class="font-semibold text-gray-900 dark:text-white">Live brand:</span>
            <span class="ml-2 inline-flex items-center gap-2">
                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: primary }"></span>
                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: secondary }"></span>
                {{ config('app.name') }}
            </span>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            <div class="font-semibold">Please review the form.</div>
            <ul class="mt-2 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Brand identity</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Logo, company details, and theme colors used across the admin, guest screens, and print layouts.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Application name</label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company name</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company email</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company address</label>
                        <textarea name="company_address" rows="3"
                                  class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('company_address', $settings['company_address']) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Logo and theme</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Users can upload a logo and choose brand colors dynamically.</p>
                </div>

                <div class="space-y-5">
                    <div class="rounded-2xl border border-dashed border-gray-200 p-5 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Company logo" class="h-18 w-18 rounded-2xl border border-gray-200 object-cover dark:border-gray-700" />
                            @else
                                <div class="flex h-18 w-18 items-center justify-center rounded-2xl text-lg font-semibold text-white"
                                     :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                    {{ strtoupper(mb_substr(old('app_name', $settings['app_name']), 0, 2)) }}
                                </div>
                            @endif

                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Current brand mark</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, or WebP up to 2MB.</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Upload logo</label>
                            <input type="file" name="company_logo"
                                   class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:file:bg-brand-500/10 dark:file:text-brand-300" />
                        </div>

                        @if($logoUrl)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                Remove current logo
                            </label>
                        @endif
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Primary color</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="brand_primary_color" x-model="primary" value="{{ old('brand_primary_color', $settings['brand_primary_color']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="primary"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Secondary color</label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-gray-700">
                                <input type="color" name="brand_secondary_color" x-model="secondary" value="{{ old('brand_secondary_color', $settings['brand_secondary_color']) }}" class="h-11 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0" />
                                <input type="text" x-model="secondary"
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm uppercase text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Live preview</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Preview updates before you save.</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: primary }"></span>
                                <span class="h-3 w-3 rounded-full border border-white/40" :style="{ backgroundColor: secondary }"></span>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between px-4 py-4 text-white"
                                 :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/18 text-sm font-semibold">
                                        {{ strtoupper(mb_substr(old('app_name', $settings['app_name']), 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">{{ old('app_name', $settings['app_name']) }}</div>
                                        <div class="text-xs text-white/80">Admin navigation and dashboards</div>
                                    </div>
                                </div>
                                <button type="button" class="rounded-lg bg-white/16 px-3 py-2 text-xs font-semibold">Action</button>
                            </div>
                            <div class="grid gap-3 bg-gray-50 p-4 dark:bg-gray-950/40">
                                <div class="rounded-xl bg-white p-4 shadow-theme-xs dark:bg-gray-900">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sidebar active item</div>
                                    <div class="mt-3 inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white"
                                         :style="{ background: `linear-gradient(135deg, ${primary}, ${secondary})` }">
                                        Settings
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Currency</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default accounting and reporting display values.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Currency code</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', $settings['currency_code']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Currency symbol</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">SMS gateway</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Saved for outbound SMS provider integration.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider name</label>
                        <input type="text" name="sms_provider" value="{{ old('sms_provider', $settings['sms_provider']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sender ID</label>
                        <input type="text" name="sms_sender_id" value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Base URL</label>
                        <input type="text" name="sms_base_url" value="{{ old('sms_base_url', $settings['sms_base_url']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">API key</label>
                        <input type="text" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">API secret</label>
                        <input type="password" name="sms_api_secret" value="{{ old('sms_api_secret', $settings['sms_api_secret']) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP email</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Runtime mail settings for outgoing email and notifications.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP host</label>
                    <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP port</label>
                    <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Encryption</label>
                    <select name="smtp_encryption"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">No encryption</option>
                        <option value="tls" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'ssl')>SSL</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP username</label>
                    <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SMTP password</label>
                    <input type="password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mail from address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div class="md:col-span-2 xl:col-span-1">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mail from name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                           class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                Save settings
            </button>
        </div>
    </form>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Database backup and restore</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Create SQL snapshots of the live database, download them, and restore the system from a saved backup.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.settings.backups.create') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    Create backup
                </button>
            </form>
        </div>

        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
            Restoring a backup will replace the current database state. Use restore only when you intentionally want to roll back to a previous snapshot.
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-950/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Backup file</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400">Size</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse($backups as $backup)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $backup['filename'] }}</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">SQL snapshot stored in local backup storage.</div>
                                </td>
                                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-300">
                                    {{ $backup['last_modified_at']->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-300">
                                    {{ $backup['size_label'] }}
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.settings.backups.download', ['filename' => $backup['filename']]) }}"
                                           class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                            Download
                                        </a>
                                        <form method="POST" action="{{ route('admin.settings.backups.restore') }}"
                                              onsubmit="return confirm('Restore this backup and overwrite the current database?');">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                            <input type="hidden" name="confirm_restore" value="1">
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-xs font-semibold text-error-700 hover:bg-error-100 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                                                Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No database backups have been created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
