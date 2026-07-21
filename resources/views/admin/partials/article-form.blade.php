{{-- Tampilan: article form --}}
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="title" name="title"
           value="{{ old('title', $article->title ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="author" class="form-label">Author</label>
        <input type="text" class="form-control" id="author" name="author"
               value="{{ old('author', $article->author ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="category" class="form-label">Category</label>
        <select class="form-select" id="category" name="category">
            <option value="">-- Select category --</option>
            @foreach(['Logistics', 'Trade', 'Shipping', 'Economy', 'Geopolitics'] as $cat)
                <option value="{{ $cat }}" @selected(old('category', $article->category ?? '') === $cat)>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="image_url" class="form-label">Image URL</label>
    <input type="url" class="form-control" id="image_url" name="image_url"
           placeholder="https://example.com/image.jpg"
           value="{{ old('image_url', $article->image_url ?? '') }}">
</div>

<div class="mb-3">
    <label for="tags" class="form-label">Tags <small class="text-muted">(comma-separated)</small></label>
    <input type="text" class="form-control" id="tags" name="tags"
           placeholder="port congestion, red sea, inflation"
           value="{{ old('tags', isset($article) && $article->tags ? implode(', ', $article->tags) : '') }}">
</div>

<div class="mb-3">
    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
    <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content', $article->content ?? '') }}</textarea>
</div>

<div class="mb-3 form-check form-switch">
    <input type="hidden" name="is_published" value="0">
    <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1"
           @checked(old('is_published', $article->is_published ?? true))>
    <label class="form-check-label" for="is_published">Published (visible to users)</label>
</div>
