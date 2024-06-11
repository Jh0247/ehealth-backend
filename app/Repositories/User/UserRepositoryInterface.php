<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
