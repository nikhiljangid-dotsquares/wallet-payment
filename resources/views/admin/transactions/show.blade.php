@extends('admin::admin.layouts.master')

@section('title', 'Wallet Transactions Management')

@section('page-title', 'Wallet Transaction Details')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.transactions.index') }}">Wallet Transaction Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Wallet Transaction Details</li>
@endsection

@section('content')
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Email Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">                    
                    <div class="table-responsive">
                         <div class="card-body">      
                            <table class="table table-striped">
                                <tbody>                  
                                    <tr>
                                        <th scope="row">Name</th>
                                        <td scope="col">{{ $transaction->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Type</th>
                                        <td scope="col">
                                            {{ ucfirst($transaction->type) ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Amount</th>
                                        <td scope="col">${{ number_format($transaction->amount, 2) ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td scope="col"> {{ ucfirst($transaction->status) }}</td>
                                    </tr>    
                                    <tr>
                                        <th scope="row">Created At</th>
                                        <td scope="col"> {{ $transaction->created_at
                                            ? $transaction->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                            : '—' }}</td>
                                    </tr>                                
                                </tbody>
                            </table>
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Back</a> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End transactions Content -->
    </div>
    <!-- End Container fluid  -->
@endsection
