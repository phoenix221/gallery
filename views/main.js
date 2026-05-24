$(document).ready(function () {
    $('#upload_image').on('submit', function(e){
        e.preventDefault();

        let formData = new FormData(this);
        $.ajax({
            url: '/api/images',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                let result = JSON.parse(response);
                $('#message').text(result.success).addClass("alert alert-success");
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                $('#message').text(result.error);
            }
        });
    });

    $('#get_images').on('click', function(e){
        e.preventDefault();
        let limit = 10;
        let offset = 0;
        loadImages(limit, offset);
    });

    $('#next').on('click', function() {
        let limit = parseInt(
            $(this).data('limit')
        );
        let offset = parseInt(
            $(this).data('offset')
        );
        offset += limit;
        $(this).data('offset', offset);
        $('#back').data('offset', offset);
        loadImages(limit, offset);
    });

    $('#back').on('click', function() {
        let limit = parseInt(
            $(this).data('limit')
        );
        let offset = parseInt(
            $(this).data('offset')
        );
        offset -= limit;
        $(this).data('offset', offset);
        $('#next').data('offset', offset);
        loadImages(limit, offset);
    });
});

function deleteImage(id){

    $.ajax({
        url: '/api/images/'+id,
        method: 'DELETE',
        processData: false,
        contentType: false,
        success: function (response) {
            console.log(response);
            let result = JSON.parse(response);
            $('#message').text(result.success);
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            let result = JSON.parse(response);
            $('#message').text(result.error);
        }
    });
}

function getInfo(id){

    $.ajax({
        url: '/api/images/'+id,
        method: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {
            console.log(response);
            let result = JSON.parse(response);
            let html = '';
            html += `
                <p>ID: ${result.data.id}</p>
                <p>Name: ${result.data.filename}</p>
                <p>Size: ${result.data.size}</p>
                <p>Type: ${result.data.mime_type}</p>
                <p>URL: ${result.data.s3_url}</p>
            `;
            $('#info').html(html);
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            let result = JSON.parse(response);
            $('#message').text(result.error);
        }
    });
}

function loadImages(limit, offset){
    let url = "/api/images?limit="+limit+"&offset="+offset
    $.ajax({
        url: url,
        method: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {
            console.log(response);
            let result = JSON.parse(response);
            let html = '';
            result.data.forEach(function(image) {
                html += `
                        <div class="card" style="width: 18rem;">
                          <div class="card-body">
                            <h5 class="card-title">${image.filename}</h5>
                            <p id="info"></p>
                            <button onclick="getInfo(${image.id})" class="btn btn-primary">Получить информацию</button>
                            <button onclick="deleteImage(${image.id})" class="btn btn-danger">Удалить</button>
                          </div>
                        </div>
                    `;
            });

            $('#block_images').html(html);
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            $('#message').text(result.error);
        }
    });
}
