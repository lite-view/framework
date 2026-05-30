<?php


namespace LiteView\Support;


use LiteView\Kernel\Visitor;

abstract class ApiResourceController
{
    abstract public function index(Visitor $visitor);

    abstract public function store(Visitor $visitor);

    abstract public function show(Visitor $visitor, $id);

    abstract public function update(Visitor $visitor, $id);

    abstract public function destroy(Visitor $visitor, $id);
}