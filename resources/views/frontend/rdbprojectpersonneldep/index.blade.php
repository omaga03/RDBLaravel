@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            Rdbprojectpersonneldep List
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->getKey() }}</td>
                        <td>
                            <a href="{{ route('frontend.rdbprojectpersonneldep.show', $item->getKey()) }}" class="btn btn-info btn-sm">View</a>
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