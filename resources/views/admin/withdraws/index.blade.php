@extends('admin::admin.layouts.master')

@section('title', 'Wallet Withdrawal Requests')

@section('page-title', 'Wallet Withdrawal Manager')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Wallet Withdrawal Manager</li>
@endsection

@section('content')

@php
    $statuses = ['pending', 'approved', 'declined', 'cancelled'];
    $selectedStatus = app('request')->query('status');
@endphp

    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start withdrawal Content -->
        <div class="row">
            <div class="col-12">
                <div class="card card-body">
                    <h4 class="card-title">Filter</h4>
                    <form action="{{ route('admin.withdraws.index') }}" method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="keyword">Name</label>
                                    <input type="text" name="keyword" id="keyword" class="form-control"
                                        value="{{ app('request')->query('keyword') }}" placeholder="Enter name">                                   
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control select2">
                                        <option value="" {{ is_null($selectedStatus) || $selectedStatus === '' ? 'selected' : '' }}>
                                                Select status
                                        </option>

                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>                                   
                                </div>
                            </div>
                            <div class="col-auto mt-1 text-right">
                                <div class="form-group">
                                    <label for="created_at">&nbsp;</label>
                                    <button type="submit" form="filterForm" class="btn btn-primary mt-4">Filter</button>
                                    <a href="{{ route('admin.withdraws.index') }}" class="btn btn-secondary mt-4">Reset</a>
                                </div>
                            </div>
                        </div>                       
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">S. No.</th>
                                        <th scope="col">@sortablelink('name', 'Name', [], ['class' => 'text-dark'])</th>
                                        <th scope="col">@sortablelink('amount', 'Amount', [], ['class' => 'text-dark'])</th>
                                        <th scope="col">@sortablelink('status', 'Status', [], ['class' => 'text-dark'])</th>
                                        <th scope="col">@sortablelink('created_at', 'Created At', [], ['class' => 'text-dark'])</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($withdrawList) && $withdrawList->count() > 0)
                                        @php
                                            $i = ($withdrawList->currentPage() - 1) * $withdrawList->perPage() + 1;
                                        @endphp
                                        @foreach ($withdrawList as $withdraw)
                                            <tr>
                                                <th scope="row">{{ $i }}</th>
                                                <td>{{ $withdraw->user->name ?? 'N/A' }}</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($withdraw->amount, 2) }}</td>
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
                                                        <a href="javascript:void(0)"
                                                            data-url="{{ route('admin.withdraws.changeStatus', $withdraw->id) }}"
                                                            data-id="{{ $withdraw->id }}"
                                                            data-status="{{ $withdraw->status }}"
                                                            class="btn btn-{{ $statusClass[$withdraw->status] ?? 'secondary' }} btn-sm"
                                                            onclick="openModelToChangeStatus(this, '{{ $withdraw->status }}')">
                                                            {{ $label }}
                                                        </a>
                                                    @else
                                                        <span class="badge badge-{{ $statusClass[$withdraw->status] ?? 'secondary' }}">
                                                            {{ $label }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $withdraw->created_at
                                                        ? $withdraw->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.withdraws.show', $withdraw->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <h6>No records Found</h6>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!--pagination move the right side-->
                            @if ($withdrawList->count() > 0)
                                {{ $withdrawList->links('admin::pagination.custom-admin-pagination') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection