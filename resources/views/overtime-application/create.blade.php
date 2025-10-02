@extends('layouts.admin')

@section('title', 'Create Overtime Application')

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

    .btn-submit:hover {
        background-color: #7a2f2f;
    }

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

    .btn-back:hover {
        background-color: #888;
    }

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
    <div class="header-with-icon">
        <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"
             xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                  d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
        </svg>
        Create Overtime Application
    </div>
@endsection

@section('content')
    <div class="form-container">
        <h2>New Overtime Application</h2>

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

        <form action="{{ route('overtime-applications.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="employee_id">Employee</label>
                <select name="employee_id" id="employee_id" required>
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }}
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
                       value="{{ old('start_datetime') ? \Carbon\Carbon::parse(old('start_datetime'))->format('Y-m-d\TH:i') : '' }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="end_datetime">End Date & Time</label>
                <input type="datetime-local" 
                       name="end_datetime" 
                       id="end_datetime" 
                       class="form-control" 
                       value="{{ old('end_datetime') ? \Carbon\Carbon::parse(old('end_datetime'))->format('Y-m-d\TH:i') : '' }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            {{-- ✅ Tasks --}}
            <div class="form-group">
                <label>Tasks</label>
                <div id="tasks-wrapper">
                    <div class="task-item">
                        <input type="text" name="tasks[]" placeholder="Enter task description" value="" required>
                        <button type="button" class="btn-remove-task" onclick="removeTask(this)">-</button>
                    </div>
                </div>
                <button type="button" class="btn-add-task" onclick="addTask()">+ Add Task</button>
            </div>

            <div class="form-group d-flex justify-content-between">
                <a href="{{ route('overtime-applications.index') }}" class="btn-back">Back</a>
                <button type="submit" class="btn-submit">Submit</button>
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
