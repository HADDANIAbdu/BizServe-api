@extends('layouts.app')

@section('Content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container d-flex justify-content-center align-items-center vh-10">
    <div class="card p-4 bg-light text-dark rounded" style="max-width: 400px; width: 100%;">
        <form action="{{ route('auth.register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="exampleInputName" class="form-label">Nom complet</label>
                <input type="text" id="name" name="name" class="form-control" >
            </div>
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" >
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" id="password" >
            </div>
            <div class="mb-3">
                <label for="exampleInputPasswordConfirmation" class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1" >
                <label class="form-check-label" for="exampleCheck1">J'accepte toutes les conditions</label>
            </div>
            <button type="submit" class="btn btn-success">Register</button>
        </form>
    </div>
</div>
@endsection
