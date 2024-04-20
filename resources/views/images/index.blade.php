@extends('template')

@section('title', "Загрузить фото")

@section('content')
    <h2 class="mt-5">
        Загрузить фото
    </h2>
    <div class="row mt-3 mb-3">
        <form class="" action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group ">
                <div class="col-10">
                    <input type="file" name="images[]" multiple required class="form-control">
                </div>
                <div class="col-2">
                    <button class="form-control btn btn-info" type="submit">Загрузить</button>
                </div>
            </div>
        </form>
    </div>
    <h3>API</h3>
    <ul>
        <li><a href="https://romadelukin.000webhostapp.com/public/api/v1/get-list">Список</a></li>
        <li><a href="https://romadelukin.000webhostapp.com/public/api/v1/view/1">Один файл</a></li>
    </ul>
@endsection
