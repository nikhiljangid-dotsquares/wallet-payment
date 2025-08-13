@extends('backend.layouts.app')
@section('title', 'Withdrawal Requests')
@section('content')

@php
$methodDetails = json_decode($withdraw->method_details);
$statusClass = 'secondary';
if($withdraw->status == 'approved') {
    $statusClass = 'success';
}
if($withdraw->status == 'declined') {
    $statusClass = 'danger';
}
@endphp
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">View Withdrawal Request</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item "><a href="{{ route('admin.withdraws.index') }}">Withdrawal Requests</a>
                    </li>
                    <li class="breadcrumb-item active">View Withdrawal Request</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="card-title" style="width:100%;display: inline-block;">
                                <a href="{{ route('admin.withdraws.index') }}">
                                    <button type="button" class="btn btn-primary btn-sm float-right">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </button>
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
                                    <tr>
                                        <th colspan="4"><u>Bank Details:</u></th>
                                    </tr>
                                    @foreach ($methodDetails ?? [] as $key => $value)
                                    <tr>
                                        <th>{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                        <td colspan="3">{{ $value ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
   
</div>
@endsection