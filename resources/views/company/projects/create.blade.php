<x-company-layout title="New Project">

    <div style="max-width:600px;">
        <a href="{{ route('company.projects.index') }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-block; margin-bottom:16px;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">← Back to Projects</a>

        <div class="ptm-card" style="padding:24px;">
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:20px;">Create New Project</div>

            <form method="POST" action="{{ route('company.projects.store') }}" style="display:flex; flex-direction:column; gap:16px;">
                @csrf

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PROJECT NAME *</label>
                    <input type="text" name="name" value="{{ old('name') }}" autofocus class="ptm-input"
                           style="{{ $errors->has('name') ? 'border-color:rgba(248,113,113,0.5);' : '' }}">
                    @error('name')<div style="font-size:11px; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                    <textarea name="description" rows="3" class="ptm-input" style="resize:vertical;">{{ old('description') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS *</label>
                        <select name="status" class="ptm-select">
                            @foreach(['planning'=>'Planning','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('status','planning') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">START DATE</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="ptm-input">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="ptm-input"
                               style="{{ $errors->has('due_date') ? 'border-color:rgba(248,113,113,0.5);' : '' }}">
                        @error('due_date')<div style="font-size:11px; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex; gap:10px; padding-top:6px;">
                    <button type="submit" class="ptm-btn-primary">Create Project</button>
                    <a href="{{ route('company.projects.index') }}" class="ptm-btn-ghost" style="text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-company-layout>
