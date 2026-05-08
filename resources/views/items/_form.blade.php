@php($editing = isset($item))

<div
    class="space-y-6"
    data-item-report-form
    data-form-stepper
    data-initial-step="{{ old('wizard_step', 1) }}"
>
    <input type="hidden" name="wizard_step" value="{{ old('wizard_step', 1) }}" data-step-input>

    <section class="form-progress-card">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="form-section-kicker">Step-by-step guide</p>
                <h3 class="form-section-title">Report progress</h3>
            </div>
            <p class="text-sm text-slate-600" data-step-summary>Step 1 of 4: Report basics</p>
        </div>

        <div class="form-progress-steps mt-4">
            <div class="form-progress-step" data-step-indicator="1">
                <span class="form-progress-number" data-step-marker>1</span>
                <span class="form-progress-text">Basics</span>
            </div>
            <div class="form-progress-step" data-step-indicator="2">
                <span class="form-progress-number" data-step-marker>2</span>
                <span class="form-progress-text">Where & when</span>
            </div>
            <div class="form-progress-step" data-step-indicator="3">
                <span class="form-progress-number" data-step-marker>3</span>
                <span class="form-progress-text">Photo</span>
            </div>
            <div class="form-progress-step" data-step-indicator="4">
                <span class="form-progress-number" data-step-marker>4</span>
                <span class="form-progress-text">Contact</span>
            </div>
        </div>
    </section>

    <section class="form-section-card" data-form-step="1" data-step-title="Report basics">
        <div class="form-section-heading">
            <div>
                <p class="form-section-kicker">Section 1</p>
                <h3 class="form-section-title">Report basics</h3>
            </div>
            <p class="form-section-text">Choose the report type and name the item clearly.</p>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="label">Report type</label>
                <select name="type" class="field" required data-report-type>
                    <option value="lost" @selected(old('type', $item->type ?? request('type', 'lost')) === 'lost')>Lost</option>
                    <option value="found" @selected(old('type', $item->type ?? request('type')) === 'found')>Found</option>
                </select>
            </div>

            <div>
                <label class="label">Category</label>
                <select name="category" class="field" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $item->category ?? '') === $category)>{{ str($category)->headline() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="label">Item title</label>
                <input type="text" name="title" value="{{ old('title', $item->title ?? '') }}" class="field" placeholder="Example: Black wallet near library" required>
            </div>

            <div class="md:col-span-2">
                <label class="label">Description</label>
                <textarea name="description" rows="5" class="field" placeholder="Describe the color, brand, contents, marks, or anything that helps identify the item." required>{{ old('description', $item->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="form-step-actions mt-6">
            <a href="{{ route('dashboard') }}" class="secondary-button">Cancel</a>
            <button type="button" class="primary-button" data-step-next>Next section</button>
        </div>
    </section>

    <section class="form-section-card" data-form-step="2" data-step-title="Where and when" hidden>
        <div class="form-section-heading">
            <div>
                <p class="form-section-kicker">Section 2</p>
                <h3 class="form-section-title">Where and when</h3>
            </div>
            <p class="form-section-text">Tell users where the item was seen and when it was reported.</p>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="label">Location</label>
                <input type="text" name="location" value="{{ old('location', $item->location ?? '') }}" class="field" placeholder="Example: Computer Laboratory 3" required>
            </div>

            <div>
                <label class="label">Date and time reported</label>
                <input type="datetime-local" name="reported_at"
                    value="{{ old('reported_at', isset($item) ? $item->reported_at->format('Y-m-d\\TH:i') : now()->format('Y-m-d\\TH:i')) }}"
                    class="field" required>
            </div>

            @if ($editing && ($canEditStatus ?? false))
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="field" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $item->status ?? 'open') === $status)>{{ \App\Models\Item::statusLabelFor($status) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div data-reward-group>
                <label class="label">Reward amount</label>
                <input type="number" name="reward_amount" step="0.01" min="0" value="{{ old('reward_amount', $item->reward_amount ?? '') }}" class="field" data-reward-input>
                <p class="mt-2 text-xs text-slate-500" data-reward-help>Use this only for lost item reports when the owner is offering a reward.</p>
            </div>
        </div>

        <div class="form-step-actions mt-6">
            <button type="button" class="secondary-button" data-step-prev>Previous</button>
            <button type="button" class="primary-button" data-step-next>Next section</button>
        </div>
    </section>

    <section class="form-section-card" data-form-step="3" data-step-title="Item photo" hidden>
        <div class="form-section-heading">
            <div>
                <p class="form-section-kicker">Section 3</p>
                <h3 class="form-section-title">Item photo</h3>
            </div>
            <p class="form-section-text">A clear image makes it easier for others to recognize the item.</p>
        </div>

        <div class="mt-5">
            <label class="label">Upload item photo</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/*" class="field file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:font-semibold file:text-blue-700" data-image-input data-preview-target="item-photo-preview" data-preview-label="Item photo preview">
            <p class="mt-2 text-xs text-slate-500">Accepted files: JPG, PNG, WEBP. Maximum size: 3MB.</p>

            <div class="image-preview-card mt-4" id="item-photo-preview" data-preview-card @if (empty($item?->photo_src)) hidden @endif>
                <p class="image-preview-label">Item photo preview</p>
                <img
                    src="{{ $item->photo_src ?? '' }}"
                    alt="Item photo preview"
                    class="image-preview-frame"
                    data-preview-image
                    @if (empty($item?->photo_src)) hidden @endif
                >
                <p class="image-preview-empty" data-preview-empty @if (! empty($item?->photo_src)) hidden @endif>
                    Select an image to preview it before submitting.
                </p>
            </div>
        </div>

        <div class="form-step-actions mt-6">
            <button type="button" class="secondary-button" data-step-prev>Previous</button>
            <button type="button" class="primary-button" data-step-next>Next section</button>
        </div>
    </section>

    <section class="form-section-card" data-form-step="4" data-step-title="Contact details" hidden>
        <div class="form-section-heading">
            <div>
                <p class="form-section-kicker">Section 4</p>
                <h3 class="form-section-title">Contact details</h3>
            </div>
            <p class="form-section-text">These details help users and administrators reach you about the case.</p>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="label">Contact name</label>
                <input type="text" name="contact_name" value="{{ old('contact_name', $item->contact_name ?? auth()->user()->name) }}" class="field">
            </div>

            <div>
                <label class="label">Contact email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $item->contact_email ?? auth()->user()->email) }}" class="field">
            </div>

            <div class="md:col-span-2">
                <label class="label">Contact phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $item->contact_phone ?? auth()->user()->phone) }}" class="field">
            </div>
        </div>

        <div class="form-step-actions mt-6">
            <button type="button" class="secondary-button" data-step-prev>Previous</button>
            <button type="submit" class="primary-button">{{ $editing ? 'Save changes' : 'Submit report' }}</button>
        </div>
    </section>
</div>
