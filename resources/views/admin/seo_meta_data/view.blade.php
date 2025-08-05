<!-- card section -->
<div class="card bg-white">
    <!--start card header section -->
    <div class="card-header bg-white border-bottom border-gray-200">
        <h4 class="card-title">Seo Meta </h4>
    </div>
    <div class="table-responsive">
        <div class="card-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th scope="row">Meta Title</th>
                        <td scope="col">{{ $seo->meta_title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Meta keywords</th>
                        <td scope="col">{{ $seo->meta_keywords ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Meta Description</th>
                        <td scope="col">{{ $seo->meta_description ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
