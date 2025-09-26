@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>System Settings</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#general" class="list-group-item active">General Settings</a>
                <a href="#academic" class="list-group-item">Academic Settings</a>
                <a href="#financial" class="list-group-item">Financial Settings</a>
                <a href="#email" class="list-group-item">Email Configuration</a>
                <a href="#security" class="list-group-item">Security Settings</a>
                <a href="#backup" class="list-group-item">Backup Settings</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>General Settings</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Institution Name</label>
                            <input type="text" class="form-control" value="IntelliCampus University">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <select class="form-control">
                                <option>2024-2025</option>
                                <option>2025-2026</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Term</label>
                            <select class="form-control">
                                <option>Fall 2024</option>
                                <option>Spring 2025</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection