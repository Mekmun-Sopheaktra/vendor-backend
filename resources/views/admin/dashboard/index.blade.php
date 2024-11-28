@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-5">Admin Dashboard</h2>

        <div class="row">
            <!-- Total Users Card -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-white bg-primary shadow-sm h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-users fa-2x mr-3"></i>
                        <span>Total Users</span>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <h5 class="card-title display-4">{{ $totalUsers }}</h5>
                    </div>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-white bg-success shadow-sm h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-shopping-cart fa-2x mr-3"></i>
                        <span>Total Orders</span>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <h5 class="card-title display-4">{{ $totalOrders }}</h5>
                    </div>
                </div>
            </div>

            <!-- Total Products Card -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-white bg-warning shadow-sm h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-box-open fa-2x mr-3"></i>
                        <span>Total Products</span>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <h5 class="card-title display-4">{{ $totalProducts }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Top 5 Best-Selling Products</h4>
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                    <tr>
                        <th>Product Name</th>
                        <th>Total Sold</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stats as $stat)
                        <tr>
                            <td>{{ $stat->product_name }}</td>
                            <td>{{ $stat->total }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
