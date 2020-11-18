<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use PhpParser\Node\Expr\Cast\Object_;
use Restserver\Libraries\REST_Controller;

class Users extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    // menampilkan data
    function index_get()
    {
        $username = $this->get('username');
        $users = [];
        if ($username == '') {
            $this->db->select('u.id as id_user,g.id as id_groups, u.nama_lengkap, u.username, u.password, g.nama as nama_groups');
            $this->db->join('users_groups ug', 'ug.users_id = u.id');
            $this->db->join('groups g', 'g.id = ug.groups_id');
            $data = $this->db->get('users u');
            $jumlah = $data->num_rows();
            foreach ($data->result() as $key) :
                $users[] = [
                    'id_user' => $key->id_user,
                    'username' => $key->username,
                    'nama_lengkap' => $key->nama_lengkap,
                    'password' => $key->password,
                    'groups' => $key->nama_groups,
                    '_links' => [(object)[
                        "href" => "usergroups/{$key->id_groups}",
                        "rel" => "usergroups",
                        "type" => "GET"
                    ]]
                ];
            endforeach;
        } else {
            $this->db->select('u.id as id_user,g.id as id_groups, u.nama_lengkap, u.username, u.password, g.nama as nama_groups');
            $this->db->join('users_groups ug', 'ug.users_id = u.id', 'left');
            $this->db->join('groups g', 'g.id = ug.groups_id', 'left');
            $this->db->where('u.username', $username);
            $data = $this->db->get('users u');
            $jumlah = $data->num_rows();
            foreach ($data->result() as $key) :
                $users[] = [
                    'id_user' => $key->id_user,
                    'username' => $key->username,
                    'nama_lengkap' => $key->nama_lengkap,
                    'password' => $key->password,
                    'groups' => $key->nama_groups,
                    '_links' => [(object)[
                        "href" => "usergroups/{$key->id_groups}",
                        "rel" => "usergroups",
                        "type" => "GET"
                    ]]
                ];
            endforeach;
        }

        $user = empty($users) ? 'data kosong' : $users;

        $result = [
            "took" => $_SERVER["REQUEST_TIME_FLOAT"],
            "code" => 200,
            "message" => "Response Succesfully",
            "num"   => $jumlah,
            "data" => $user
        ];
        $this->response($result, 200);
    }

    // menambah data
    function index_post()
    {
        $data = array(
            'users_id'      => $this->post('users_id'),
            'groups_id'  => $this->post('groups_id')
        );

        $insert = $this->db->insert('users_groups', $data);

        if ($insert) {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 201,
                "message" => 'Respone Successfully Users Added',
                "data"  => $data
            ];
            $this->response($result, 201);
        } else {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 502,
                "message" => 'Respone Failed Users Added',
                "data"  => null
            ];
            $this->response($result, 502);
        }
    }

    // update data
    function index_put()
    {
        $id = $this->put('users_id');
        $data = array(
            'groups_id'  => $this->put('groups_id')
        );
        $this->db->where('users_id', $id);
        $update = $this->db->update('users_groups', $data);
        if ($update) {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 202,
                "message" => 'Respone Successfully Update',
                "data"  => $data
            ];
            $this->response($result, 202);
        } else {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 502,
                "message" => 'Respone Failed Update',
                "data"  => null
            ];
            $this->response($result, 502);
        }
    }

    // hapus data
    function index_delete()
    {
        $id = $this->delete('users_id');
        $this->db->where('users_id', $id);
        $delete = $this->db->delete('users_groups');
        if ($delete) {
            $this->response(array('status' => 'success'), 201);
        } else {
            $this->response(array('status' => 'fail'), 502);
        }
    }
}
