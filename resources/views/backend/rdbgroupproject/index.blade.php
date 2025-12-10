@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            Rdbgroupproject List
            <a href="{{ route('backend.rdbgroupproject.create') }}" class="btn btn-primary btn-sm float-end">Create New</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->getKey() }}</td>
                        <td>
                            <a href="{{ route('backend.rdbgroupproject.show', $item->getKey()) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('backend.rdbgroupproject.edit', $item->getKey()) }}" class="btn btn-warning btn-sm">Edit</a>
                             <form action="{{ route('backend.rdbgroupproject.destroy', $item->getKey()) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection