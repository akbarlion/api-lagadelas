<?php

defined('BASEPATH') or exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');
date_default_timezone_set("UTC");

require APPPATH . 'libraries/Authentication.php';
require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

// use chriskacerguis\RestServer\Format;
use chriskacerguis\RestServer\RestController;
use Reservation\Libraries\Authentication;

class Api extends RestController
{
    // function __construct()
    // {
    //     parent::__construct();
    //     $this->load->model('m_api', 'api');
    //     $this->load->helper('download');
    // }

    public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
        $this->load->model('m_api', 'api');
        $this->load->helper('download');
    }


    public function createJWT($username, $password)
    {
        $token_data['USERNAME'] = $username;
        $token_data['PASSWORD'] = $password;
        $tokenData = $this->authorization_token->generateToken($token_data);
        return $tokenData;
    }
    private function sendJson($data)
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8')->set_output(json_encode($data));
    }

    public function sessionList_get()
    {
        $session_data = $this->api->get_all('session');
        if ($session_data) {
            $this->response([
                'status' => true,
                'message' => 'Berhasil Menampilkan Data',
                'data' => $session_data
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal Menampilkan Data'
            ], self::HTTP_BAD_REQUEST);
        }
    }

    public function roleList_get()
    {
        $data_role = $this->api->get_all('role_access');
        if ($data_role > 0) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Menampilkan Data Role Access',
                'data' => $data_role
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 404,
                'message' => 'Gagal Menampilkan Data'
            ], self::HTTP_NOT_FOUND);
        }
    }

    public function adminList_get()
    {
        $data_user_panitia = $this->api->select_panitia('*', 'account_panitia', 'role_access', 'account_panitia.ACCESS_ROLE', 'role_access.ID_ROLE');
        if ($data_user_panitia > 0) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Menampilkan Data Panitia',
                'data' => $data_user_panitia
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 404,
                'message' => 'Gagal Menampilkan Data'
            ], self::HTTP_NOT_FOUND);
        }
    }

    public function sessionUpdate_post()
    {
        $param = $this->post();
        $id_session = $param['ID_SESSION'];
        $pin_session = $param['SESSION_PIN'];
        $name_session = $param['SESSION_NAME'];
        $desc = $param['DESCRIPTION'];
        $date_sesi = $param['SESSION_DATE'];
        $start = $param['SESSION_START'];
        $end = $param['SESSION_END'];

        $data_where = [
            'ID_SESSION' => $id_session,
            'SESSION_PIN' => $pin_session,
        ];
        $data_update_session = [
            'SESSION_NAME' => $name_session,
            'DESCRIPTION' => $desc,
            'SESSION_DATE' => $date_sesi,
            'SESSION_START' => $start,
            'SESSION_END' => $end
        ];
        $update_sesi = $this->api->update_data('session', $data_update_session, $data_where);
        if ($update_sesi > 0) {
            $this->response([
                'status' => true,
                'message' => 'Berhasil Update Sesi',
                'data' => $update_sesi
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal Update Sesi'
            ], self::HTTP_BAD_REQUEST);
        }

    }

    public function getQuestion_post()
    {
        $param = $this->post();
        $pin = $param['SESSION_PIN'];
        $question = $this->api->question_pupuk($pin);
        if ($question > 0) {
            $this->response([
                'status' => true,
                'message' => "Berhasil Menampilkan Data",
                'data' => $question
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => "Gagal Menampilkan Data"
            ], self::HTTP_NOT_FOUND);
        }
    }

    public function login_post()
    {
        $param = $this->post();
        $USERNAME = $param['USERNAME'];
        $PASSWORD = $param['PASSWORD'];
        $login = $this->api->checking_user($USERNAME, $PASSWORD);

        if ($login) { // Check if $login is not empty
            $pin = $param['PIN'];
            $result = $this->api->checking_pin($pin);

            if ($result) {
                $id_user = $this->api->getting_user($USERNAME, $PASSWORD);
                if ($id_user) {
                    $this->response([
                        'status' => 200,
                        'message' => 'Berhasil Menemukan Data',
                        'data' => $result,
                        'login' => $id_user
                    ], self::HTTP_OK);
                } else {
                    $this->response([
                        'status' => 404,
                        'message' => 'Gagal Menemukan Data'
                    ], self::HTTP_NOT_FOUND);
                }
            } else {
                $this->response([
                    'status' => 404,
                    'message' => 'Gagal Mendapatkan Data',
                    'data' => null
                ], self::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => 404,
                'message' => 'Gagal Mendapatkan Data',
                'data' => null
            ], self::HTTP_NOT_FOUND);
        }
    }
    public function createPinSession_post()
    {
        $param = $this->post();
        $SESSION_NAME = $param['SESSION_NAME'];
        $DESCRIPTION = $param['DESCRIPTION'];
        $SESSION_DATE = $param['SESSION_DATE'];
        $SESSION_START = $param['SESSION_START'];
        $SESSION_END = $param['SESSION_END'];

        $SESSION_PIN = '';
        for ($i = 0; $i < 4; $i++) {
            $SESSION_PIN .= rand(0, 9);
        }

        $data_pin = [
            "SESSION_NAME" => $SESSION_NAME,
            "SESSION_PIN" => $SESSION_PIN,
            "DESCRIPTION" => $DESCRIPTION,
            "SESSION_DATE" => $SESSION_DATE,
            "SESSION_START" => $SESSION_START,
            "SESSION_END" => $SESSION_END
        ];
        $pin_success = $this->api->insert_data('session', $data_pin);
        if ($pin_success) {
            $this->response([
                'status' => true,
                'message' => 'Berhasil Menambahkan Pin',
                'data' => $pin_success
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal Menambahkan Pin'
            ], self::HTTP_BAD_REQUEST);
        }
    }

    public function insertJawabanPupuk_post()
    {
        $param = $this->post();
        $id_user = $param['ID_USER'];
        $id_question = $param['ID_QUESTION'];

        $data_where = [
            'ID_USER' => $id_user,
            'ID_QUESTION' => $id_question
        ];

        $data_jawaban = [
            'ID_QUESTION' => $id_question,
            'ID_OPTIONS' => $this->post('ID_OPTIONS'),
            'TIME_COUNTDOWN' => $this->post('TIMECOUNTDOWN'),
            'ID_USER' => $this->post('ID_USER')
        ];

        $checking_data = $this->api->select_where('responses_pupuk', $data_where);
        if ($checking_data) {
            $data_update_jawaban = $this->api->update_data('responses_pupuk', $data_jawaban, $data_where);
            if ($data_update_jawaban > 0) {
                $this->response([
                    'status' => 200,
                    'message' => 'Berhasil Update Jawaban',
                    'data' => $data_update_jawaban
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => 500,
                    'message' => 'Gagal Update Jawaban'
                ], self::HTTP_BAD_REQUEST);
            }
        } else {
            $data_insert_jawaban = $this->api->insert_data('responses_pupuk', $data_jawaban);
            if ($data_insert_jawaban) {
                $this->response([
                    'status' => 200,
                    'message' => 'Berhasil Menambahkan Jawaban Baru',
                    'data' => $data_insert_jawaban
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => 500,
                    'message' => 'Gagal Update Jawaban'
                ], self::HTTP_BAD_REQUEST);
            }
        }

    }

    public function createSoalSandi_post()
    {
        $param = $this->post();
        $QUESTION_TEXT = $param['QUESTION_TEXT'];
        $SESSION_PIN = $param['SESSION_PIN'];
        $QUESTION_IMAGE = $param['QUESTION_IMAGE'];
        $K1 = $param['K1'];
        $K2 = $param['K2'];
        $K3 = $param['K3'];
        $K4 = $param['K4'];
        $K5 = $param['K5'];

        $data_question_sandi = [
            'QUESTION_TEXT' => $QUESTION_TEXT,
            'SESSION_PIN' => $SESSION_PIN,
            'QUESTION_IMAGE' => $QUESTION_IMAGE
        ];


        $data_question = $this->api->insert_get_id('question', $data_question_sandi);

        $data_kunci_sandi = [
            'ID_QUESTION' => $data_question,
            'K1' => $K1,
            'K2' => $K2,
            'K3' => $K3,
            'K4' => $K4,
            'K5' => $K5,
        ];

        if ($data_question > 0) {
            $data_kunci = $this->api->insert_data('kunci_sandi', $data_kunci_sandi);
            if ($data_kunci > 0) {
                $this->response([
                    'status' => 200,
                    'message' => 'Berhasil Menambahkan Data Kunci',
                    'data' => $data_kunci
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => 500,
                    'message' => 'Gagal Menambahkan Data Kunci'
                ], self::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Gagal Menambahkan Soal'
            ], self::HTTP_BAD_REQUEST);
        }

    }

    public function insertAccount_post()
    {
        $param = $this->post();
        $USERNAME = $param['USERNAME'];
        $PASSWORD = md5($param['PASSWORD']);
        $FULL_NAME = $param['FULL_NAME'];
        $DIVISI = $param['DIVISI'];
        $EMAIL = $param['EMAIL'];
        $ACCESS_ROLE = $param['ACCESS_ROLE'];
        $CREATED_AT = $param['CREATED_AT'];

        $data_post_panitia = [
            'USERNAME' => $USERNAME,
            'PASSWORD' => $PASSWORD,
            'FULL_NAME' => $FULL_NAME,
            'DIVISI' => $DIVISI,
            'EMAIL' => $EMAIL,
            'ACCESS_ROLE' => $ACCESS_ROLE,
            'CREATED_AT' => $CREATED_AT,
        ];

        $data_panitia = $this->api->insert_data('account_panitia', $data_post_panitia);
        if ($data_panitia > 0) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Menambahkan Data Panitia',
                'data' => $data_panitia
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Gagal Menambahkan Data Panitia'
            ], self::HTTP_BAD_REQUEST);
        }

    }

    public function loginPanitia_post()
    {
        $param = $this->post();
        $USERNAME = $param['USERNAME'];
        $PASSWORD = md5($param['PASSWORD']);

        $login_panitia = $this->api->checking_panitia($USERNAME, $PASSWORD);
        if ($login_panitia) {
            $this->response([
                'token' => $this->createJWT($USERNAME, $PASSWORD),
                'status' => 200,
                'message' => "Berhasil Menampilkan Data",
                'data' => $login_panitia
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 500,
                'message' => "Gagal Menampilkan Data"
            ], self::HTTP_NOT_FOUND);
        }
    }













}