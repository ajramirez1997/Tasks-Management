<?php

namespace App\Http\Controllers;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        return view('tasks');

    }

    public function table(Request $request)
    {
        if ($request->ajax()) {

            $data = Tasks::select(['id', 'title', 'content', 'images', 'status', 'is_draft', 'created_at'])
                ->where(function ($query){
                    $query->whereNull('main_task_id')->orWhere('main_task_id', '');
                })
                ->where('user_id', auth()->id());
            
            if($request->input('status')){

                if($request->input('status') === '1'){

                    $data->where('is_draft', '1');

                }else{
                    
                    $data->where('status', $request->input('status'));
                    $data->where('is_draft', '0');

                }

            }
    
            return DataTables::of($data)
                ->addColumn('subtask', function($row) {
                    $count = Tasks::where('user_id', auth()->id())->where('main_task_id', $row->id)->count();

                    if($count > 0){
                        return '<button type="button" title="Show Sub Task" class="btn p-0 show-sub-task-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-plus"></i></button>';
                    }
                    return '';

                })
                ->addColumn('action', function($row) {

                    if ($row->is_draft === '1') {
                        return '
                            <button type="button" title="Save Draft" class="btn p-0 in-progress-status-btn" data-id="' . $row->id . '">
                            <i class="fa-solid fa-floppy-disk"></i></button>
                            <button type="button" title="Add a Sub Task" class="btn p-0 add-sub-btn" data-id="' . $row->id . '">
                            <i class="fa-solid fa-square-plus"></i></button>
                            <button type="button" title="View Task" class="btn p-0 view-btn" data-id="' . $row->id . '">
                            <i class="fa-solid fa-eye "></i></button>
                            <button type="button" title="Edit Task" class="btn p-0 edit-btn" data-bs-toggle="modal" data-bs-target="#editTasksModal" data-id="' . $row->id . '">
                            <i class="fa-solid fa-square-pen "></i></button>
                            <button type="button" title="Delete Task" class="btn p-0 delete-btn" data-id="' . $row->id . '">
                            <i class="fa-solid fa-square-xmark "></i></button>
                        ';
                    }

                    $HtmlBtn = '';
                    $HtmlBtn .= '
                        <button type="button" title="Add a Sub Task" class="btn p-0 add-sub-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-square-plus"></i></button>
                    ';

                    if ($row->status == 'to-do') {

                        $HtmlBtn .= '
                        <button type="button" title="Change The Status to In Progress" class="btn p-0 in-progress-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-bars-progress "></i></button>
                        <button type="button" title="Change The Status to Done" class="btn p-0 done-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-square-check "></i></button>
                        ';

                    }elseif ($row->status == 'in-progress') {

                        $HtmlBtn .= '
                        <button type="button" title="Change The Status to To-Do" class="btn p-0 to-do-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-list-check "></i></button>
                        <button type="button" title="Change The Status to Done" class="btn p-0 done-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-square-check "></i></button>';

                    }elseif ($row->status == 'done') {

                        $HtmlBtn .= '
                        <button type="button" title="Change The Status to To-Do" class="btn p-0 to-do-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-list-check "></i></button>
                        <button type="button" title="Change The Status to In Progress" class="btn p-0 in-progress-status-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-bars-progress "></i></button>';

                    }

                    $HtmlBtn .= '
                        <button type="button" title="View Task" class="btn p-0 view-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-eye "></i></button>
                        <button type="button" title="Edit Task" class="btn p-0 edit-btn" data-bs-toggle="modal" data-bs-target="#editTasksModal" data-id="' . $row->id . '">
                        <i class="fa-solid fa-square-pen "></i></button>
                        <button type="button" title="Delete Task" class="btn p-0 delete-btn" data-id="' . $row->id . '">
                        <i class="fa-solid fa-square-xmark "></i></button>
                        ';

                    return $HtmlBtn;
                })
                ->editColumn('status', function($row) {

                    if ($row->is_draft === '1') {

                        return '<span class="badge rounded-pill bg-info text-dark badge-size d-flex justify-content-center align-items-center">Draft</span>';

                    }

                    switch ($row->status) {
                        case 'to-do':
                            return '<span class="badge rounded-pill bg-warning text-dark badge-size d-flex justify-content-center align-items-center">To-Do</span>';
                        case 'in-progress':
                            return '<span class="badge rounded-pill bg-secondary badge-size d-flex justify-content-center align-items-center">In Progress</span>';
                        case 'done':
                            return '<span class="badge rounded-pill bg-success badge-size d-flex justify-content-center align-items-center">Done</span>';
                        default:
                            return '<span class="badge rounded-pill bg-secondary badge-size d-flex justify-content-center align-items-center">Unknown</span>';
                    }
                })
                ->editColumn('created_at', function($row) {
                    return $row->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['subtask', 'action', 'images', 'status'])
                ->make(true);
        }
    
        return response()->json(['status' => 'error', 'error' => 'Unauthenticated'], 401);
    }
    
    public function subtaskTable(Request $request)
    {

        if($request->ajax()){

            $data = Tasks::select(['id', 'title', 'content', 'images', 'status', 'is_draft', 'created_at'])
                ->where('user_id', auth()->id())
                ->where('main_task_id', $request->input('id'))
                ->where('is_draft', '0')
                ->get();

            $data = collect($data);
            $data = $data->map(function ($item){

                // $date = new DateTime($item->created_at);
                // $item->date = $date->format('Y-m-d H:i');
                
                $item->date = date('Y-m-d H:i', strtotime($item->created_at));

                $HtmlBtn = '';

                if($item->status === 'to-do'){
                    $item->status = '<span class="badge rounded-pill bg-warning text-dark badge-size d-flex justify-content-center align-items-center">To-Do</span>';
                    $HtmlBtn .= '
                    <button type="button" title="Change The Status to In Progress" class="btn p-0 in-progress-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-bars-progress "></i></button>
                    <button type="button" title="Change The Status to Done" class="btn p-0 done-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-square-check "></i></button>
                    ';
                }elseif ($item->status === 'in-progress') {
                    $item->status = '<span class="badge rounded-pill bg-secondary badge-size d-flex justify-content-center align-items-center">In Progress</span>';
                    $HtmlBtn .= '
                    <button type="button" title="Change The Status to To-Do" class="btn p-0 to-do-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-list-check "></i></button>
                    <button type="button" title="Change The Status to Done" class="btn p-0 done-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-square-check "></i></button>';
                }elseif ($item->status === 'done') {
                    $item->status = '<span class="badge rounded-pill bg-success badge-size d-flex justify-content-center align-items-center">Done</span>';
                    $HtmlBtn .= '
                    <button type="button" title="Change The Status to To-Do" class="btn p-0 to-do-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-list-check "></i></button>
                    <button type="button" title="Change The Status to In Progress" class="btn p-0 in-progress-status-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-bars-progress "></i></button>';
                }
                
                $HtmlBtn .= '
                    <button type="button" title="View Task" class="btn p-0 view-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-eye "></i></button>
                    <button type="button" title="Edit Task" class="btn p-0 edit-btn" data-bs-toggle="modal" data-bs-target="#editTasksModal" data-id="' . $item->id . '">
                    <i class="fa-solid fa-square-pen "></i></button>
                    <button type="button" title="Delete Task" class="btn p-0 delete-btn" data-id="' . $item->id . '">
                    <i class="fa-solid fa-square-xmark "></i></button>
                    ';
                
                $item->action = $HtmlBtn;

                if($item->images){

                    $images = json_decode($item->images);
                    if(count($images) > 0){

                        $item->images = '<div id="carousel-' .$item->id. '" class="carousel slide">
                        <div class="carousel-inner">
                        ';
                        foreach ($images as $key => $value) {
                            $item->images .= '
                                <div class="carousel-item' . ($key === 0 ? ' active' : '') . '">
                                    <img src="' . asset('resources/images/' . $value) . '" class="d-block w-100 fixed-image" alt="..." data-bs-toggle="modal" data-bs-target="#imageModal" data-image="' . asset('resources/images/' . $value) . '">
                                </div>
                            ';
                        }
                        
                        $item->images .= '</div>';
                        $item->images .= '
                            <a class="carousel-control-prev" href="#carousel-' .$item->id. '" role="button" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carousel-' .$item->id. '" role="button" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>';
    
                    }else{
                        
                        $item->images = '<p>No images available</p>';

                    }
                }else{

                    $item->images = '<p>No images available</p>';

                }

                return $item;

            });

            return response()->json(['success' => true, 'data' => $data], 200);
            
        }

        return response()->json(['status' => 'error', 'error' => 'Unauthenticated'], 401);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:100|unique:tasks,title',
                'content' => 'required|string',
                'status' => 'required|string|in:to-do,in-progress,done',
                'is_draft' => 'sometimes|in:0,1',    
                'file.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'message' =>  $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = auth()->id();
            
            $imagePaths = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $image) {
                    $ext = $image->getClientOriginalExtension();
                    $filename = auth()->id()."-".Str::random(12).".".$ext;
                    
                $path = $image->move(public_path('resources/images'), $filename);
                    $imagePaths[] = $filename;
                }
            }

            try {

                $task = Tasks::create([
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                    'user_id' => $data['user_id'],
                    'is_draft' => $data['is_draft'] ?? '0',
                    'images' => json_encode($imagePaths),
                ]);
                return response()->json(['status' => 'success', 'data' => $data, 'message' => "Success Saving Task Details"], 201);

            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'data' => $data, 'message' => $e], 500);

            }
        }

        return response()->json(['status' => 'error', 'error' => 'Unauthenticated', 'message' => "Unauthenticated"], 401);

    }

    public function subtask(Request $request)
    {

        if ($request->ajax()) {

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'title' => 'required|string|max:100|unique:tasks,title',
                'content' => 'required|string',
                'status' => 'required|string|in:to-do,in-progress,done',
                'file.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'message' =>  $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = auth()->id();
            
            $imagePaths = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $image) {
                    $ext = $image->getClientOriginalExtension();
                    $filename = auth()->id()."-".Str::random(12).".".$ext;
                    
                $path = $image->move(public_path('resources/images'), $filename);
                    $imagePaths[] = $filename;
                }
            }

            try {

                $task = Tasks::create([
                    'main_task_id' => $data['id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                    'user_id' => $data['user_id'],
                    'images' => json_encode($imagePaths),
                ]);
                return response()->json(['status' => 'success', 'data' => $data, 'message' => "Success Saving Sub Task Details"], 201);

            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'data' => $data, 'message' => $e], 500);

            }
        }

        return response()->json(['status' => 'error', 'error' => 'Unauthenticated', 'message' => "Unauthenticated"], 401);

    }


    public function status(Request $request, $id)
    {
        if ($request->ajax()) {

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:to-do,in-progress,done'
            ]);

            if ($validator->fails()) {

                return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'message' => 'Validation failed'], 422);

            }

            $data = $validator->validated();

            $update = Tasks::findOrFail($id);

            if ($data['status'] === 'done') {

                //Check if there's a main_task_id column.
                if(filled($update['main_task_id'])){

                    $update->update($data);
                    $SubTaskIds = Tasks::where('user_id', auth()->id())->where('main_task_id', $update['main_task_id']);

                    //Compare if all the subtask is done if all done update the main_task_id to done also.
                    if($SubTaskIds->count() === $SubTaskIds->where('status', 'done')->count()){

                        Tasks::where('id', $update['main_task_id'])->update(['status' => $data['status']]);

                    }

                }else{

                    //If main task is done all the sub task well be done also.
                    $SubTaskIds = Tasks::select('id')->where('user_id', auth()->id())->where('main_task_id', $id)->pluck('id');

                    if($SubTaskIds->count() > 0){

                        $update->update($data);
                        Tasks::whereIn('id', $SubTaskIds)->update(['status' => $data['status']]);

                    }else{

                        $update->update($data);

                    }
                    
                }

            }else{

                $update->update($data);

            }
            try {



                return response()->json(['status' => 'success', 'data' => $update, 'message' => 'Successfully change the status!'], 200);

            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'data' => $e, 'message' => 'Failed to change status!'], 500);

            }
        }

        return response()->json(['status' => 'error', 'error' => 'Unauthenticated', 'message' => 'Unauthenticated'], 401);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $data = Tasks::find($id);

        if ($data) {

            return response()->json(['status' => 'success', 'data' => $data]);

        } else {

            return response()->json(['status' => 'error', 'data' => null, 'message' => 'Tasks not found'], 404);

        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        if ($request->ajax()) {
            $id = $request->input('id');
            $validator = Validator::make($request->all(), [
                'title' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('tasks', 'title')->where('user_id', auth()->id())->ignore($id),
                ],
                'content' => 'required|string',
                'status' => 'required|string|in:to-do,in-progress,done,draft',    
                'file.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
            ]);

            if ($validator->fails()) {

                return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'message' => 'Validation failed'], 422);

            }

            $data = $validator->validated();
            
            $imagePaths = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $image) {
                    $ext = $image->getClientOriginalExtension();
                    $filename = auth()->id()."-".Str::random(12).".".$ext;
                    
                $path = $image->move(public_path('resources/images'), $filename);
                    $imagePaths[] = $filename;
                }
            }
// dd($data);
            try {

                $update = Tasks::findOrFail($id);
                
                // Decode existing images
                if ($update->images) {
                    $existingImages = json_decode($update->images, true);
                
                    // Combine existing images with new images
                    $images = array_merge($existingImages, $imagePaths);
                }
                
                if($data['status'] === 'draft'){
                    $data['status'] = $update->status;
                    $data['is_draft'] = '1';
                }
                $update->update([
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                    'is_draft' => $data['is_draft'] ?? '0',
                    'images' => json_encode($images)
                ]);

                return response()->json(['status' => 'success', 'data' => $update, 'message' => 'Task details updated successfully'], 200);

            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'data' => $update, 'message' => $e], 500);

            }
        }

        return response()->json(['status' => 'error', 'error' => 'Unauthenticated', 'message' => 'Unauthenticated'], 401);
    }
    public function removeImage(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'filename' => 'required|string',
        ]);
    
        try {
            // Find the task by ID
            $task = Tasks::findOrFail($id);
    
            // Decode the existing images
            $images = json_decode($task->images, true);
    
            // Find and remove the image from the list
            if (($key = array_search($request->filename, $images)) !== false) {
                unset($images[$key]);
    
                // Remove the image file from storage
                $imagePath = public_path('resources/images/') . $request->filename;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
    
                // Update the task record with the new image list
                $task->update([
                    'images' => json_encode(array_values($images)), // Re-index array
                ]);
    
                return response()->json(['status' => 'success', 'message' => 'Image removed successfully'], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Image not found'], 404);
            }
    
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {

        if ($request->ajax()) {

            try {

                $data = Tasks::findOrFail($id);
                $data->delete();
    
                return response()->json(['status' => 'success', 'message' => 'Task deleted successfully'], 200);
    
            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'message' => 'Failed to delete Task'], 500);

            }

        }
    
        return response()->json(['status' => 'error', 'error' => 'Unauthenticated', 'message' => 'Unauthenticated'], 401);

    }
    
}
