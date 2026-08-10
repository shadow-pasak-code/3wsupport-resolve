<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function login($username, $password)
    {
        $user = $this->db
            ->where('username', $username)
            ->where('is_active', 1)
            ->get($this->table)
            ->row();

        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }
        return false;
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function create($data)
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_password($id, $new_password)
    {
        return $this->db->update($this->table, [
            'password_hash' => password_hash($new_password, PASSWORD_DEFAULT),
        ], ['id' => $id]);
    }
}
