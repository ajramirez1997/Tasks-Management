<div class="modal fade" id="addTasksModal" tabindex="-1" aria-labelledby="addTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTasksModalLabel">Add Tasks</h5>
                <button type="button" class="btn-close CloseAddModal"></button>
            </div>
            <div class="modal-body">
                <form id="addTasksForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="TasksTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="TasksTitle" name="TasksTitle" maxlength="100" placeholder="Enter Task Title">
                    </div>
                    <div class="mb-3">
                        <label for="TasksContent" class="form-label">Content</label>
                        <textarea class="form-control" id="TasksContent" name="TasksContent" placeholder="Enter Task Content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="TasksStatus" class="form-label">Status</label>
                        <select class="form-control" id="TasksStatus" name="TasksStatus">
                            <option value="to-do" selected>To-Do</option>
                            <option value="in-progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="taskImages" class="form-label">Images</label>
                        <div id="taskDropzone" class="dropzone"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success saved" id="DraftTasksBtn">Save As Draft</button>
                <button type="button" class="btn btn-primary saved" id="AddTasksBtn">Save</button>
                <button type="button" class="btn btn-secondary CloseAddModal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="SubModal" tabindex="-1" aria-labelledby="SubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="SubModalLabel">Add Sub Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="SubTasksForm" enctype="multipart/form-data">
                    <input type="hidden" id="MainTaskId" name="MainTaskId">
                    <div class="mb-3">
                        <label for="SubTasksTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="SubTasksTitle" name="SubTasksTitle" maxlength="100" placeholder="Enter Task Title">
                    </div>
                    <div class="mb-3">
                        <label for="SubTasksContent" class="form-label">Content</label>
                        <textarea class="form-control" id="SubTasksContent" name="SubTasksContent" placeholder="Enter Task Content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="SubTasksStatus" class="form-label">Status</label>
                        <select class="form-control" id="SubTasksStatus" name="SubTasksStatus">
                            <option value="to-do" selected>To-Do</option>
                            <option value="in-progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="SubTaskDropzone" class="form-label">Images</label>
                        <div id="SubTaskDropzone" class="dropzone"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="SubTasksBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">View Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="ViewTasksTitle" class="form-label">Title</label>
                    <input type="text" class="form-control readonly-input" id="ViewTasksTitle" readonly>
                </div>
                <div class="mb-3">
                    <label for="ViewTasksContent" class="form-label">Content</label>
                    <input type="text" class="form-control readonly-input" id="ViewTasksContent" readonly>
                </div>
                <div class="mb-3">
                    <label for="ViewTasksStatus" class="form-label">Status</label>
                    <input type="text" class="form-control readonly-input" id="ViewTasksStatus" readonly>
                </div>

                <label class="form-label">Images</label>
                <div id="imageCarousel" class="carousel slide">
                    <div class="carousel-inner" id="carouselInner">
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTasksModal" tabindex="-1" aria-labelledby="editTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTasksModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTasksForm" enctype="multipart/form-data">
                    <input type="hidden" id="editTasksId" name="editTasksId">
                    <div class="mb-3">
                        <label for="editTasksTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTasksTitle" name="title" maxlength="100" placeholder="Enter Task Title">
                    </div>
                    <div class="mb-3">
                        <label for="editTasksContent" class="form-label">Content</label>
                        <textarea class="form-control" id="editTasksContent" name="content" placeholder="Enter Task Content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTasksStatus" class="form-label">Status</label>
                        <select class="form-control" id="editTasksStatus" name="status">
                            <option value="to-do">To-Do</option>
                            <option value="in-progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskImages" class="form-label">Images</label>
                        <div id="editTaskDropzone" class="dropzone"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="EditTasksBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center">
                <img id="modalImage" src="" class="img-fluid" alt="...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

