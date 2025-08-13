@extends('backend.layouts.app')
@section('title', 'Withdrawal Requests')
@section('content')

@php
    $statuses = ['pending', 'approved', 'declined', 'cancelled'];
    $selectedStatus = request('status');
@endphp

<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor"> Withdrawal Requests</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Withdrawal Requests</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-inline mb-3">
                                <form action="{{ route('admin.withdraws.index') }}" method="get"
                                    class="w-100 d-flex align-items-center">
                                    <div class="input-group flex-grow-1 col-md-4">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search fa-fw"></i></span>
                                        </div>
                                        <input type="text" name="search"
                                            value="{{ request('search') }}" class="form-control"
                                            placeholder="Search by name">
                                    </div>
                                    <div class="form-group mb-0 col-md-2">
                                        <select name="status" class="form-control" style="width:100%">
                                            <option value="" {{ $selectedStatus === null || $selectedStatus === '' ? 'selected' : '' }}>
                                                Select status
                                            </option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 ml-2">
                                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                                        <a href="{{ route('admin.withdraws.index') }}"
                                            class="btn btn-secondary">Reset</a>
                                    </div>
                                </form>
                            </div>
                            <br>
                            <div class="table-responsive">
                                <table id="user" class="table table-bordered table-hover">
                                    <thead>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>@sortablelink('created_at', 'Created At')</th>
                                        <th>@sortablelink('updated_at', 'Updated At')</th>
                                        <th>Actions</th>
                                    </thead>
                                    <tbody>
                                        @if($withdrawList->count() > 0)
                                            @php
                                                $i = ($withdrawList->perPage() * ($withdrawList->currentPage() - 1));
                                            @endphp
                                            @foreach($withdrawList as $withdraw)
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $withdraw->user->name ?? 'N/A' }}</td>
                                                    <td>{{ config('GET.currency_sign')}}{{$withdraw->amount }}</td>
                                                    <td>
                                                        @php
                                                            $statusClass = [
                                                                'approved' => 'success',
                                                                'pending' => 'primary',
                                                                'declined' => 'danger',
                                                                'cancelled' => 'danger',
                                                            ];
                                                            $label = ucfirst($withdraw->status);
                                                        @endphp

                                                        @if($withdraw->status === 'pending')
                                                            <button class="btn btn-{{ $statusClass[$withdraw->status] ?? 'secondary' }} btn-sm"
                                                                onclick="openModelToChangeStatus({{ $withdraw->id }}, '{{ $withdraw->status }}')">
                                                                {{ $label }}
                                                            </button>
                                                        @else
                                                            <span class="badge badge-{{ $statusClass[$withdraw->status] ?? 'secondary' }}">
                                                                {{ $label }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ date(config('GET.admin_date_format'), strtotime($withdraw->created_at)) }}</td>
                                                    <td>{{ $withdraw->updated_at ? date(config('GET.admin_date_format'), strtotime($withdraw->updated_at)) : 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.withdraws.show', $withdraw->id) }}">
                                                            <button class="btn btn-info btn-sm" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                        <tr>
                                            <td colspan="8">
                                                <h6>
                                                    <center>No records Found</center>
                                                </h6>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if($withdrawList->total() > 0)
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="entries-info">
                                    Showing {{ $withdrawList->firstItem() }} to {{ $withdrawList->lastItem() }} of
                                    {{ $withdrawList->total() }} entries
                                </div>
                                <div class="pagination-wrapper float-right">
                                    {{ $withdrawList->links('vendor.pagination.bootstrap-4') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection