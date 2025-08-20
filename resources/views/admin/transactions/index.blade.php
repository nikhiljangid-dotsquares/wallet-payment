@extends('admin::admin.layouts.master')

@section('title', 'Wallet Transactions Management')

@section('page-title', 'Wallet Transaction Manager')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Wallet Transaction Manager</li>
@endsection

@php
    $statuses = ['pending', 'succeeded', 'failed'];
    $selectedStatus = app('request')->query('status');
@endphp

@section('content')
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start transaction Content -->
        <div class="row">
            <div class="col-12">
                <div class="card card-body">
                    <h4 class="card-title">Filter</h4>
                    <form action="{{ route('admin.transactions.index') }}" method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="keyword" id="keyword" class="form-control"
                                        value="{{ app('request')->query('keyword') }}" placeholder="Enter title">                                   
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
                                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary mt-4">Reset</a>
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
                                        <th scope="col">@sortablelink('name', 'Name', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                        <th scope="col">@sortablelink('type', 'Type', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                        <th scope="col">@sortablelink('amount', 'Amount', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                        <th scope="col">@sortablelink('status', 'Status', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                        <th scope="col">@sortablelink('created_at', 'Created At', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($transactions) && $transactions->count() > 0)
                                        @php
                                            $i = ($transactions->currentPage() - 1) * $transactions->perPage() + 1;
                                        @endphp
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <th scope="row">{{ $i }}</th>
                                                <td>{{ $transaction->user->name ?? '' }}</td>
                                                <td>
                                                    {{ ucfirst($transaction->type) }}
                                                </td>
                                                <td>${{ number_format($transaction->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $transaction->status == 'succeeded' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $transaction->created_at
                                                        ? $transaction->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                                        : '—' }}
                                                </td>
                                                <td style="width: 10%;">
                                                    <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="View this record"
                                                        class="btn btn-warning btn-sm"><i class="mdi mdi-eye"></i></a>
                                                </td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!--pagination move the right side-->
                            @if ($transactions->count() > 0)
                                {{ $transactions->links('admin::pagination.custom-admin-pagination') }}
                            @endif                        
                            
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!-- End transaction Content -->
    </div>
    <!-- End Container fluid  -->
@endsection
