<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mt-5">
                    <h1 class="mb-4 text-center">To Do List</h1>
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                        
                        
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 d-flex align-items-center mb-4">
                            <button class="btn btn-success me-3" data-bs-toggle="modal" data-bs-target="#addTasksModal">
                                Add Tasks
                            </button>

                            <label for="FilterTaskStatus" class="form-label mb-0 me-2">Status: </label>
                            <div class="d-flex align-items-center">
                                <select class="form-control me-2 w-auto" id="FilterTaskStatus" name="FilterTaskStatus" style="min-width: 150px;">
                                    <option value="" selected></option>
                                    <option value="to-do">To-Do</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="done">Done</option>
                                    <option value="1">Draft</option>
                                </select>
                            </div>
                        </div>


                            
                            <table id="TasksTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modals.tasks-modal')

    <script>

        $(document).ready(function() {
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table;
            function InitializeDataTable(selectedStatus = NULL){
                
                table = $('#TasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("tasks.table") }}',
                        type: 'GET',
                        data: {
                            status: selectedStatus
                        }
                    },
                    columns: [
                        { data: 'subtask', orderable: false, searchable: false },
                        { data: 'created_at', searchable: false },
                        { data: 'title' },
                        { data: 'content', orderable: false, searchable: false },
                        {
                            data: 'images',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
            
                                if (!data) {
                                    return '<p>No images available</p>';
                                }
                                let images;
                                try {
                                    images = JSON.parse(data);
                                } catch (e) {
                                    return '<p>Error parsing images</p>';
                                }
            
                                if (!Array.isArray(images) || images.length === 0) {
                                    return '<p>No images available</p>';
                                }
            
                                const carouselId = 'carousel-' + meta.row;
            
                                let carouselHTML = `<div id="${carouselId}" class="carousel slide">`;
                                carouselHTML += '<div class="carousel-inner">';
            
                                images.forEach((image, index) => {
                                    carouselHTML += `
                                        <div class="carousel-item${index === 0 ? ' active' : ''}">
                                            <img src="{{ asset('resources/images/') }}/${image}" class="d-block w-100 fixed-image" alt="..." data-bs-toggle="modal" data-bs-target="#imageModal" data-image="{{ asset('resources/images/') }}/${image}">
                                        </div>`;
                                });
            
                                carouselHTML += '</div>';
                                carouselHTML += `
                                    <a class="carousel-control-prev" href="#${carouselId}" role="button" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#${carouselId}" role="button" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </a>
                                </div>`;
            
                                return carouselHTML;
                            }
                        },
                        { data: 'status', orderable: false, searchable: false },
                        { data: 'action', orderable: false, searchable: false }
                    ],
                    columnDefs: [
                        {    
                            targets: 0,
                            render: function(data, type, row){
                                return '<div class="d-flex justify-content-center align-items-center">'+ data +'</div>';
                            }

                        }
                    ],
                    order: [
                        [1, 'asc']
                    ],
                });

            }
        
            InitializeDataTable($('#FilterTaskStatus').val());
            $('#FilterTaskStatus').on('change', function() {
                
                const selectedStatus = $(this).val();
                $('#TasksTable').DataTable().clear().destroy();
                InitializeDataTable(selectedStatus);

            });

            $('#imageModal').on('show.bs.modal', function (event) {
        
                var button = $(event.relatedTarget);
                var imageUrl = button.data('image'); 
                var modal = $(this);
                modal.find('#modalImage').attr('src', imageUrl);
        
            });
        
            $("#TasksTable").on("click", ".add-sub-btn", function(){
                
                const TasksId = $(this).data('id');
                $("#MainTaskId").val(TasksId);
                $("#SubModal").modal("show");

            });

            $("#TasksTable").on("click", ".to-do-status-btn", function(){
        
                const TasksId = $(this).data('id');
                const Status = 'to-do';
                ChangeStatus(TasksId, Status);
        
            });
        
            $("#TasksTable").on("click", ".in-progress-status-btn", function(){
        
                const TasksId = $(this).data('id');
                const Status = 'in-progress';
                ChangeStatus(TasksId, Status);
        
            });
        
            $("#TasksTable").on("click", ".done-status-btn", function(){
        
                const TasksId = $(this).data('id');
                const Status = 'done';
                ChangeStatus(TasksId, Status);
        
            });
        
            function ChangeStatus(TasksId, Status){
        
                $.ajax({
                    type: "PUT",
                    url: "{{ route('tasks.status', ':id') }}".replace(':id', TasksId),
                    data: {
                        status: Status,
                    },
                    success: function(response){
                        if (response.status == "success") {
                            
                            toastr.success(response.message);
                            $('#TasksTable').DataTable().ajax.reload();
        
                        }else{
        
                            toastr.error(response.message);
        
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred: ' + xhr.responseText);
                    }
                });
        
            }
        
            $("#TasksTable").on("click", ".view-btn", function(){
        
                const TasksId = $(this).data('id');
                $.ajax({
                    type: "GET",
                    url: "{{ route('tasks.show', ':id') }}".replace(':id', TasksId),
                    success: function(response){
                        if (response.status == "success") {
        
                            $("#ViewTasksTitle").val(response.data.title);
                            $("#ViewTasksContent").val(response.data.content);
                            $("#ViewTasksStatus").val(response.data.status.toUpperCase());
                            if (response.data.images) {
        
                                const images = JSON.parse(response.data.images);
                                const carouselInner = $('#carouselInner');
                                carouselInner.empty();
        
                                images.forEach((image, index) => {
                                    const isActive = index === 0 ? 'active' : '';
                                    const carouselItem = `
                                        <div class="carousel-item ${isActive}">
                                            <img src="{{ asset('resources/images/') }}/${image}" class="d-block w-100" alt="Image ${index + 1}">
                                        </div>
                                    `;
                                    carouselInner.append(carouselItem);
                                });
                                
                            }
                            
                            $("#viewModal").modal("show");
        
                        }else{
        
                            toastr.error(response.message);
                            
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred: ' + xhr.responseText);
                    }
                });
        
            });
            
            $("#TasksTable").on("click", ".delete-btn", function(){
        
                const TasksId = $(this).data('id');
        
                Swal.fire({
                    title: "Are you sure you want to delete this Tasks?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                    if (result.isConfirmed) {
        
                        $.ajax({
                            type: "DELETE",
                            url: "{{ route('tasks.delete', ':id') }}".replace(':id', TasksId),
                            success: function(response) {
                                if (response.status === "success") {
        
                                    $('#TasksTable').DataTable().ajax.reload();
                                    toastr.success(response.message);
        
                                } else {
        
                                    toastr.error(response.message);
        
                                }
                            },
                            error: function(xhr) {
                                
                                toastr.error('An error occurred: ' + xhr.responseText);
                                
                            }
                        });
        
                    }
                });
        
            });
        
            $("#DeleteTasksBtn").on("click", function() {
        
                const TasksId = $("#DeleteTasksId").val();
        
            });
        
    
            $("#TasksTable").on("click", ".show-sub-task-btn", function(e){
                
                const TasksId = $(this).data('id');
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if ($(this).find('.fa-solid').hasClass('fa-minus')) {
                    
                    $(this).find('.fa-solid').removeClass('fa-minus');
                    $(this).find('.fa-solid').addClass('fa-plus');
                    row.child.hide();
                    tr.removeClass('shown');
                    
                }else{

                    $(this).find('.fa-solid').removeClass('fa-plus');
                    $(this).find('.fa-solid').addClass('fa-minus');
                    var columnIndex = $(this).closest('td').index();
                    loadSubTask(row, tr, columnIndex, TasksId);

                }

            });

            function loadSubTask(row, tr, columnIndex, TasksId){
                $.ajax({
                    type: "POST",
                    url: "{{ route('tasks.subtaskTable') }}",
                    data: {
                        id: TasksId,
                        columnIndex: columnIndex
                    },
                    success: function(response){
                        console.log(response);
                        if (response.success) {
                            
                            row.child(format(response.data)).show();
                            tr.addClass('shown');

                        }else{
        
                            toastr.error(response.message);
        
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred: ' + xhr.responseText);
                    }
                });
            }

            function format(data){
                
                childRows = [];

                data.forEach(function (item) {
                    var childRow = $('<tr>'+
                    '<td></td>'+
                    '<td>' + item.date + '</td>'+
                    '<td>' + item.title + '</td>'+
                    '<td>' + item.content + '</td>'+
                    '<td>' + item.images + '</td>'+
                    '<td>' + item.status + '</td>'+
                    '<td>' + item.action + '</td>'+
                    '</tr>');

                    childRows.push(childRow.toArray());

                });

                return childRows;

            }

            function convertTimestamp(Timestamp){
                // Create a new Date object
                const date = new Date(Timestamp);

                // Format the date (e.g., "YYYY-MM-DD HH:MM")
                const formattedDate = date.toLocaleString().slice(0, 16).replace('T', ' ');

                return formattedDate;
            }

        });


        function SaveDraft(){
        
            $('#addTasksModal').modal('hide'); 
        
            var taskDropzone = Dropzone.forElement("#taskDropzone");
            
            if (taskDropzone.getQueuedFiles().length > 0) {
                taskDropzone.on("sendingmultiple", function(file, xhr, formData) {
                    formData.append("is_draft", '1');
                });
                taskDropzone.processQueue();
            } else {
                $.ajax({
                    type: "POST",
                    url: "{{ route('tasks.store') }}",
                    data: {
                        title: $("#TasksTitle").val(),
                        content: $("#TasksContent").val(),
                        status: $("#TasksStatus").val(),
                        is_draft: '1',
                        _token: csrfToken // Add CSRF token
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#addTasksModal').modal('hide');
                            $("#TasksTitle").val("");
                            $("#TasksContent").val("");
                            $("#TasksStatus").val($("#TasksStatus option:first").val());
                            myDropzone.removeAllFiles(true);
                            toastr.success(response.message);
                            $('#TasksTable').DataTable().ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred: ' + xhr.responseText);
                    }
                });
            }
        
        }
        
        $('.CloseAddModal').on('click', function() {
        
            Swal.fire({
                title: "Do you want to save this in draft?",
                showDenyButton: true,
                confirmButtonText: "Save as Draft",
                denyButtonText: `Close`,
                customClass: {
                    confirmButton: 'btn btn-success me-3',
                    denyButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
        
                    SaveDraft();
        
                } else if (result.isDenied) {
                    $('#addTasksModal').modal('hide');
                    toastr.error("This is not saved in draft!"); 
                }
            });
        
        });
        
        $('#DraftTasksBtn').on('click', function() {
        
            SaveDraft();
        
        });

        Dropzone.autoDiscover = false;
        
        var taskDropzone = new Dropzone("#taskDropzone", {
            url: "{{ route('tasks.store') }}",
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFiles: 10,
            maxFilesize: 4, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function() {
                var submitButton = document.getElementById("AddTasksBtn");
                var myDropzone = this;
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
                submitButton.addEventListener("click", function() {
                    if (myDropzone.getQueuedFiles().length > 0) {
                        myDropzone.processQueue();
                    } else {
                        submitForm();
                    }
                });
            
                myDropzone.on("sendingmultiple", function(file, xhr, formData) {
                    formData.append("title", $("#TasksTitle").val());
                    formData.append("content", $("#TasksContent").val());
                    formData.append("status", $("#TasksStatus").val());
                    formData.append("is_draft", '0');
                    formData.append("_token", csrfToken);
                });
            
                myDropzone.on("successmultiple", function(file, response) {
                    toastr.success('Task added successfully!');
                    $("#addTasksModal").modal("hide");
                    $("#TasksTitle").val("");
                    $("#TasksContent").val("");
                    $("#TasksStatus").val($("#TasksStatus option:first").val());
                    myDropzone.removeAllFiles(true);
                    $('#TasksTable').DataTable().ajax.reload();
                });
            
                myDropzone.on("errormultiple", function(file, response) {
                    toastr.error('Error adding task.');
                });
            }
        });
        
        function submitForm() {
            $('#addTasksModal').modal('hide');
            $.ajax({
                type: "POST",
                url: "{{ route('tasks.store') }}",
                data: {
                    title: $("#TasksTitle").val(),
                    content: $("#TasksContent").val(),
                    status: $("#TasksStatus").val(),
                },
                success: function(response){
                    if (response.status == "success") {
                        
                        $("#addTasksModal").modal("hide");
                        $("#TasksTitle").val("");
                        $("#TasksContent").val("");
                        $("#TasksStatus").val("");
        
                        toastr.success(response.message); 
                        $('#TasksTable').DataTable().ajax.reload();
        
                    }else{
        
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
        
            });
        }

        $("#TasksTable").on("click", ".edit-btn", function() {
            const TasksId = $(this).data('id');
            $.ajax({
                type: "GET",
                url: "{{ route('tasks.show', ':id') }}".replace(':id', TasksId),
                success: function(response) {
                    if (response.status == "success") {
            
                        // Populate the form fields with the task data
                        $("#editTasksId").val(response.data.id);
                        $("#editTasksTitle").val(response.data.title);
                        $("#editTasksContent").val(response.data.content);
                        $("#editTasksStatus").val(response.data.status);
            
                        // Initialize Dropzone for the edit modal
                        const editTaskDropzone = Dropzone.forElement("#editTaskDropzone");
                        $('#editTaskDropzone .dz-preview').remove();
                        $('#editTaskDropzone .dz-message').show();
            
            
            
                        // If there are existing images, add them to Dropzone
                        if (response.data.images) {
                            const images = JSON.parse(response.data.images);
            
                            images.forEach((image) => {
                                let mockFile = { name: image, size: 12345, serverId: image }; // Include serverId for reference
                                editTaskDropzone.emit("addedfile", mockFile);
                                editTaskDropzone.emit("thumbnail", mockFile, "{{ asset('resources/images/') }}/" + image);
                                editTaskDropzone.emit("complete", mockFile);
                                editTaskDropzone.files.push(mockFile);
            
                                // Add data attribute for the remove link
                                $(mockFile.previewElement).find('.dz-remove').attr('data-server-id', image);
                            });
                        }
            
                        // Open the modal
                        $("#editTasksModal").modal("show");
            
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
            });
        });
        
        Dropzone.autoDiscover = false;
        var editTaskDropzone = new Dropzone("#editTaskDropzone", {
            url: "{{ route('tasks.update') }}", // Update with your route
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFiles: 10,
            maxFilesize: 4, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function() {
            
                var submitButton = document.getElementById("EditTasksBtn");
                var myDropzone = this;
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
                submitButton.addEventListener("click", function() {
                    if (myDropzone.getQueuedFiles().length > 0) {
                        myDropzone.processQueue();
                    } else {
                        submitEditForm();
                    }
                });
            
                myDropzone.on("sendingmultiple", function(file, xhr, formData) {
                    formData.append("id", $("#editTasksId").val());
                    formData.append("title", $("#editTasksTitle").val());
                    formData.append("content", $("#editTasksContent").val());
                    formData.append("status", $("#editTasksStatus").val());
                    formData.append("_method", "PUT");
                    formData.append("_token", csrfToken);
                });
            
                myDropzone.on("successmultiple", function(file, response) {
                    toastr.success('Task updated successfully!');
                    $("#editTasksModal").modal("hide");
                    $('#TasksTable').DataTable().ajax.reload();
                });
            
                myDropzone.on("errormultiple", function(file, response) {
                    toastr.error('Error updating task.');
                });
            
                myDropzone.on("removedfile", function(file) {
                    if (file.serverId) { 
                        $.ajax({
                            type: "PATCH",
                            url: "{{ route('tasks.removeImage', ':id') }}".replace(':id', $("#editTasksId").val()),
                            data: {
                                filename: file.serverId,
                                _token: csrfToken
                            },
                            success: function(response) {
                                if (response.status === "success") {
                                    toastr.success(response.message);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('An error occurred: ' + xhr.responseText);
                            }
                        });
                    }
                });
            }
        });
        
        function submitEditForm() {
            $('#editTasksModal').modal('hide');
            $.ajax({
                type: "PUT",
                url: "{{ route('tasks.update') }}", // Update this with the appropriate route
                data: {
                    id: $("#editTasksId").val(),
                    title: $("#editTasksTitle").val(),
                    content: $("#editTasksContent").val(),
                    status: $("#editTasksStatus").val(),
                },
                success: function(response) {
                    if (response.status == "success") {
                        $("#editTasksModal").modal("hide");
                        toastr.success(response.message);
                        $('#TasksTable').DataTable().ajax.reload();
            
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
            });
        }
        

        Dropzone.autoDiscover = false;
        
        var taskDropzone = new Dropzone("#SubTaskDropzone", {
            url: "{{ route('tasks.subtask') }}",
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFiles: 10,
            maxFilesize: 4, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function() {
                var submitButton = document.getElementById("SubTasksBtn");
                var myDropzone = this;
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
                submitButton.addEventListener("click", function() {
                    if (myDropzone.getQueuedFiles().length > 0) {
                        myDropzone.processQueue();
                    } else {
                        submitSubForm();
                    }
                });
            
                myDropzone.on("sendingmultiple", function(file, xhr, formData) {
                    formData.append("id", $("#MainTaskId").val());
                    formData.append("title", $("#SubTasksTitle").val());
                    formData.append("content", $("#SubTasksContent").val());
                    formData.append("status", $("#SubTasksStatus").val());
                    formData.append("_token", csrfToken);
                });
            
                myDropzone.on("successmultiple", function(file, response) {
                    toastr.success('Sub Task added successfully!');
                    $("#SubModal").modal("hide");
                    $("#SubTasksTitle").val("");
                    $("#SubTasksContent").val("");
                    $("#SubTasksStatus").val($("#SubTasksStatus option:first").val());
                    myDropzone.removeAllFiles(true);
                    $('#TasksTable').DataTable().ajax.reload();
                });
            
                myDropzone.on("errormultiple", function(file, response) {
                    toastr.error('Error adding sub task.');
                });
            }
        });
        
        function submitSubForm() {
            $.ajax({
                type: "POST",
                url: "{{ route('tasks.subtask') }}",
                data: {
                    id: $("#MainTaskId").val(),
                    title: $("#SubTasksTitle").val(),
                    content: $("#SubTasksContent").val(),
                    status: $("#SubTasksStatus").val(),
                },
                success: function(response){
                    if (response.status == "success") {
                        
                        $("#SubModal").modal("hide");
                        $("#SubTasksTitle").val("");
                        $("#SubTasksContent").val("");
                        $("#SubTasksStatus").val($("#SubTasksStatus option:first").val());
        
                        toastr.success(response.message); 
                        $('#TasksTable').DataTable().ajax.reload();
        
                    }else{
        
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
        
            });
        }
    </script>

</x-app-layout>
