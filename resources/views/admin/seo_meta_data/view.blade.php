
<div class="col-4">
    <div class="card">                    
        <div class="table-responsive">
                <div class="card-body">      
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th scope="row">Meta Title</th>
                            <td scope="col">{{  $seo->meta_title ?? 'N/A' }}</td>                                   
                        </tr>                                
                        <tr>
                            <th scope="row">Meta keywords</th>
                            <td scope="col">{{ $seo->meta_keywords ?? 'N/A' }}</td>                                   
                        </tr>                                
                        <tr>
                            <th scope="row">Meta Description</th>
                            <td scope="col">{{$seo->meta_description ?? 'N/A'}}</td>
                        </tr>                                
                    </tbody>
                </table>   
            </div>
        </div>
    </div>
</div>
       