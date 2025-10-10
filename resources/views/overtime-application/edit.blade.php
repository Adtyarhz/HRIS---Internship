@extends('layouts.admin')

@section('title', 'Edit Overtime Application')

@push('styles')
<style>
    .form-container {
        background-color: #F3F1E0;
        padding: 20px;
        border-radius: 8px;
        font-family: 'Manrope', sans-serif;
    }

    .form-container h2 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-family: 'Manrope', sans-serif;
    }

    .btn-submit {
        background-color: #9A3B3B;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.3s;
    }

    .btn-submit:hover { background-color: #7a2f2f; }

    .btn-back {
        background-color: #aaa;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-back:hover { background-color: #888; }

    .task-item {
        display: flex;
        gap: 10px;
        margin-bottom: 8px;
    }

    .btn-add-task, .btn-remove-task {
        background-color: #555;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-add-task:hover { background-color: #333; }
    .btn-remove-task:hover { background-color: #a33; }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" 
             width="24" height="24" viewBox="0 0 24 24" 
             class="mr-2" fill="currentColor">
            <path d="M12 20A8 8 0 1 0 12 4a8 8 0 0 0 0 16m0-18a10 10 0 1 1 0 20
                     a10 10 0 0 1 0-20m.5 5v5.25l4.5 2.67l-.75 1.23L11 11V7h1.5Z"/>
        </svg>
        Edit Overtime Application
    </div>
@endsection

@section('content')
    <div class="form-container">
        <h2>Edit Overtime Application</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('overtime-applications.update', $application->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="employee_id">Employee</label>
                <select name="employee_id" id="employee_id" required>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $application->employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="start_datetime">Start Date & Time</label>
                <input type="datetime-local" 
                       name="start_datetime" 
                       id="start_datetime" 
                       class="form-control" 
                       value="{{ \Carbon\Carbon::parse($application->start_datetime)->format('Y-m-d\TH:i') }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="end_datetime">End Date & Time</label>
                <input type="datetime-local" 
                       name="end_datetime" 
                       id="end_datetime" 
                       class="form-control" 
                       value="{{ \Carbon\Carbon::parse($application->end_datetime)->format('Y-m-d\TH:i') }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="reason">Reason (optional)</label>
                <textarea name="reason" rows="3">{{ $application->reason }}</textarea>
            </div>

            {{-- ✅ Tasks --}}
            <div class="form-group">
                <label>Tasks (optional)</label>
                <div id="tasks-wrapper">
                    @forelse ($application->tasks as $task)
                        <div class="task-item">
                            <input type="text" name="tasks[]" value="{{ $task->task_description }}" placeholder="Enter task description">
                            <button type="button" class="btn-remove-task" onclick="removeTask(this)">-</button>
                        </div>
                    @empty
                        <div class="task-item">
                            <input type="text" name="tasks[]" placeholder="Enter task description">
                            <button type="button" class="btn-remove-task" onclick="removeTask(this)">-</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn-add-task" onclick="addTask()">+ Add Task</button>
            </div>

            <div class="form-group d-flex justify-content-between">
                <a href="{{ route('overtime-applications.index') }}" class="btn-back">Back</a>
                <button type="submit" class="btn-submit">Update</button>
            </div>
        </form>
    </div>

    <script>
        function addTask() {
            const wrapper = document.getElementById('tasks-wrapper');
            const div = document.createElement('div');
            div.classList.add('task-item');
            div.innerHTML = `
                <input type="text" name="tasks[]" placeholder="Enter task description">
                <button type="button" class="btn-remove-task" onclick="removeTask(this)">-</button>
            `;
            wrapper.appendChild(div);
        }

        function removeTask(button) {
            button.parentElement.remove();
        }
    </script>
@endsection
