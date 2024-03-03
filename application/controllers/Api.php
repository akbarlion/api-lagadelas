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
    function __construct()
    {
        parent::__construct();
        $this->load->model('m_api', 'api');
        $this->load->helper('download');
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

    }







}