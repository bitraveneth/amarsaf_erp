<div class="form-section">
    <div class="section-header">
        <h3>Basic information</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $employee->name ?? '') }}" required>
        </div>
        <div>
            <label for="department">Department</label>
            <select id="department" name="department">
                <option value="">Select department</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" {{ old('department', $employee->department ?? '') === $department ? 'selected' : '' }}>
                        {{ $department }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="job_position">Job position</label>
            <select id="job_position" name="job_position">
                <option value="">Select job position</option>
                @foreach($jobPositions as $jobPosition)
                    <option value="{{ $jobPosition }}" {{ old('job_position', $employee->job_position ?? '') === $jobPosition ? 'selected' : '' }}>
                        {{ $jobPosition }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="work_zone">Work zone</label>
            <select id="work_zone" name="work_zone">
                <option value="">Select work zone</option>
                @foreach($workZones as $workZone)
                    <option value="{{ $workZone }}" {{ old('work_zone', $employee->work_zone ?? '') === $workZone ? 'selected' : '' }}>
                        {{ $workZone }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Work contact</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="work_email">Work email</label>
            <input id="work_email" name="work_email" type="email" value="{{ old('work_email', $employee->work_email ?? '') }}">
        </div>
        <div>
            <label for="work_phone">Work phone</label>
            <input id="work_phone" name="work_phone" value="{{ old('work_phone', $employee->work_phone ?? '') }}">
        </div>
        <div>
            <label for="work_mobile">Work mobile</label>
            <input id="work_mobile" name="work_mobile" value="{{ old('work_mobile', $employee->work_mobile ?? '') }}">
        </div>
        <div>
            <label for="tags-input">Tags</label>
            <div
                class="tag-input"
                data-tag-input
                data-tag-options='@json($tagOptions ?? [])'
            >
                <div class="tag-input-chips" data-tag-chips></div>
                <input
                    id="tags-input"
                    type="text"
                    class="tag-input-field"
                    data-tag-field
                    autocomplete="off"
                    placeholder="Start typing to add tags…"
                >
                <input
                    type="hidden"
                    name="tags"
                    data-tag-hidden
                    value="{{ old('tags', isset($employee->tags) && is_array($employee->tags) ? implode(', ', $employee->tags) : '') }}"
                >
                <div class="tag-input-suggestions" data-tag-suggestions></div>
            </div>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <h3>Documents</h3>
    </div>
    <div class="form-grid">
        <div>
            <label for="photo">Photo</label>
            <input id="photo" name="photo" type="file" accept="image/*">
            @if(!empty($employee->photo_path))
                <div class="preview">
                    <small>Current:</small>
                    <img src="{{ asset('storage/'.$employee->photo_path) }}" alt="Employee photo" style="max-width: 80px; border-radius: 999px;">
                </div>
            @endif
        </div>
        <div>
            <label for="cv">CV</label>
            <input id="cv" name="cv" type="file">
            @if(!empty($employee->cv_path))
                <div class="preview">
                    <small>Current:</small>
                    <a href="{{ asset('storage/'.$employee->cv_path) }}" target="_blank" class="button-secondary">View CV</a>
                </div>
            @endif
        </div>
    </div>
</div>
