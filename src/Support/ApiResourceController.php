<?php


namespace LiteView\Support;


abstract class ApiResourceController
{
    abstract public function index();

    abstract public function store();

    abstract public function show($id);

    abstract public function update($id);

    abstract public function destroy($id);
}