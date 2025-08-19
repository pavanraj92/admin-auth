<!-- card section -->
<div class="card bg-white">
    <!--start card header section -->
    <div class="card-header bg-primary">
        <h5 class="mb-0 text-white font-bold">SEO Details</h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="font-weight-bold">Meta Title:</label>
            <p>{{ $seo->meta_title ?? 'N/A' }}</p>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Meta Description:</label>
            <p>{{ $seo->meta_description ?? 'N/A' }}</p>
        </div>
        <div class="form-group">
            <label class="font-weight-bold">Meta Keywords:</label>
            <p>{{ $seo->meta_keywords ?? 'N/A' }}</p>
        </div>
    </div>
</div>
