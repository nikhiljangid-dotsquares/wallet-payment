@extends('admin::admin.layouts.master')

@section('title', 'Wallet Withdrawal Request Details')

@section('page-title', 'Wallet Withdrawal Manager')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.withdraws.index') }}">Wallet Withdrawal Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Wallet Withdrawal Request</li>
@endsection

@section('content')

@php
$methodDetails = $withdraw->method_details ?? [];
$statusClass = 'secondary';
if($withdraw->status == 'approved') {
    $statusClass = 'success';
}
if($withdraw->status == 'declined') {
    $statusClass = 'danger';
}
@endphp

    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start withdrawal Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title">Withdrawal Request Details</h4>
                            <a href="{{ route('admin.withdraws.index') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>

                            <table id="user" class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th style="width:230px">Name</th>
                                        <td>{{ $withdraw->user->name ?? '-' }}</td>

                                        <th>Status</th>
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
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td>{{config('GET.currency_sign')}}{{ $withdraw->user->wallet->balance ?? '-' }}</td>
                                        
                                        <th>Withdraw Amount</th>
                                        <td>{{config('GET.currency_sign')}}{{ $withdraw->amount ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ date(config('GET.admin_date_format'), strtotime($withdraw->created_at)) }}
                                        </td>
                                        
                                        <th>Updated At</th>
                                        <td>{{ date(config('GET.admin_date_format'), strtotime($withdraw->updated_at)) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="width:230px">Method</th>
                                        <td>{{ ucfirst($withdraw->method) ?? '-' }}</td>
                                    </tr>
                                    @if(!empty($methodDetails) && is_array($methodDetails))
                                        <tr>
                                            <th colspan="4"><u>Bank Details:</u></th>
                                        </tr>
                                        @foreach ($methodDetails as $key => $value)
                                        <tr>
                                            <th>{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                            <td colspan="3">{{ $value ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection