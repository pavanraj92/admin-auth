<!-- card section -->
<div class="card bg-white">
    <!--start card header section -->
    <div class="card-header bg-white border-bottom border-gray-200">
        <h4 class="card-title">Seo Meta </h4>
    </div>
    <!--End card header section -->
    <!--start card body section -->
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="meta_title">SEO Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                        value="{{ old('meta_title', $seo->meta_title ?? '') }}" placeholder="Enter SEO title">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="meta_keywords">SEO Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                        value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}"
                        placeholder="Enter SEO keywords (comma separated)">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="meta_description">SEO Description</label>
                    <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                        placeholder="Enter SEO description">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
        <!--End card body section -->
    </div>
    <!-- card section -->
</div>
<!-- End card section -->
