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

    // public function __construct($config = "rest")
    // {
    //     header("Access-Control-Allow-Origin: *");
    //     header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    //     header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
    //     parent::__construct();
    //     $this->load->model('m_api', 'api');
    //     $this->load->helper('download');
    // }


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

    public function updateAccountPanitia_post()
    {
        $param = $this->post();
        $where_id = $param['ID'];

        $USERNAME = $param['USERNAME'];
        $PASSWORD = md5($param['PASSWORD']);
        $FULL_NAME = $param['FULL_NAME'];
        $DIVISI = $param['DIVISI'];
        $EMAIL = $param['EMAIL'];
        $ACCESS_ROLE = $param['ACCESS_ROLE'];
        $STATUS = $param['STATUS'];
        $MODIFIED_AT = $param['MODIFIED_AT'];

        $data_perubahan = [
            'USERNAME' => $USERNAME,
            'PASSWORD' => $PASSWORD,
            'FULL_NAME' => $FULL_NAME,
            'DIVISI' => $DIVISI,
            'EMAIL' => $EMAIL,
            'ACCESS_ROLE' => $ACCESS_ROLE,
            'STATUS' => $STATUS,
            'MODIFIED_AT' => $MODIFIED_AT
        ];
        $data_perubahan = $this->api->update_data('account_panitia', $data_perubahan, array('ID' => $where_id));
        if ($data_perubahan) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Update Data',
                'data' => $data_perubahan
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal Update Data'
            ], self::HTTP_BAD_REQUEST);
        }
    }

    public function updatePassword_post()
    {
        $param = $this->post();
        $USERNAME = $param['USERNAME'];
        $PASSWORD = md5($param['PASSWORD']);
        $NEW_PASSWORD = md5($param['NEW_PASSWORD']);
        $data_where = [
            'USERNAME' => $USERNAME,
            'PASSWORD' => $PASSWORD,
        ];
        $check_password = $this->api->select_where('account_panitia', $data_where);
        if ($check_password) {
            $update_password = $this->api->update_data('account_panitia', array('PASSWORD' => $NEW_PASSWORD), array('USERNAME' => $USERNAME));
            if ($update_password) {
                $this->response([
                    'status' => 200,
                    'message' => 'Berhasil Update Data',
                    'data' => $update_password
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => 500,
                    'message' => 'Gagal Update Data'
                ], self::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Password salah'
            ], self::HTTP_BAD_REQUEST);
        }
    }

    public function upSoalPupuk_post()
    {
        $param = $this->post();
        $QUESTION_TEXT = $param['QUESTION_TEXT'];
        $QUESTION_IMAGE = $param['QUESTION_IMAGE'];
        $SESSION_PIN = $param['SESSION_PIN'];
        $JENIS_SOAL = $param['$JENIS_SOAL'];
        $OPTIONS = $param['OPTIONS'];

        $data_question = [
            'QUESTION_TEXT' => $QUESTION_TEXT,
            'SESSION_PIN' => $SESSION_PIN,
            'QUESTION_IMAGE' => $QUESTION_IMAGE,
            'JENIS_SOAL' => $JENIS_SOAL
        ];

        $data_options = [];

        foreach ($OPTIONS as $option) {
            $data_options[] = [
                'OPTIONS_TEXT' => $option['OPTIONS_TEXT'],
                'OPTIONS_IMAGE' => $option['OPTIONS_IMAGE'],
                'VALUE' => $option['VALUE']
            ];
        }

        $result = $this->api->insert_pupuk($data_question, $data_options);
        if ($result) {
            $this->response([
                'status' => true,
                'message' => 'Berhasil input data',
                'data' => $result
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal input data / ada kesalahan',
                'data' => null
            ], self::HTTP_BAD_REQUEST);
        }
    }

    public function createSoalSemboyan_post()
    {
        $param = $this->post();
        $QUESTION_TEXT = $param['QUESTION_TEXT'];
        $SESSION_PIN = $param['SESSION_PIN'];
        $QUESTION_IMAGE = $param['QUESTION_IMAGE'];
        $JENIS_SOAL = $param['JENIS_SOAL'];
        $K1 = $param['K1'];
        $K2 = $param['K2'];
        $K3 = $param['K3'];
        $K4 = $param['K4'];
        $K5 = $param['K5'];
        $K6 = $param['K6'];
        $K7 = $param['K7'];
        $K8 = $param['K8'];
        $K9 = $param['K9'];
        $K10 = $param['K10'];

        $data_question = [
            'QUESTION_TEXT' => $QUESTION_TEXT,
            'SESSION_PIN' => $SESSION_PIN,
            'QUESTION_IMAGE' => $QUESTION_IMAGE,
            'JENIS_SOAL' => $JENIS_SOAL
        ];

        $question_uploaded = $this->api->insert_get_id('question', $data_question);
        if ($question_uploaded) {

            $data_kunci_semboyan = [
                'ID_QUESTION' => $question_uploaded,
                'K1' => $K1,
                'K2' => $K2,
                'K3' => $K3,
                'K4' => $K4,
                'K5' => $K5,
                'K6' => $K6,
                'K7' => $K7,
                'K8' => $K8,
                'K9' => $K9,
                'K10' => $K10
            ];

            $data_upload_kunci = $this->api->insert_data('kunci_semboyan', $data_kunci_semboyan);
            if ($data_kunci_semboyan) {
                $this->response([
                    'status' => 200,
                    'message' => 'Berhasil Insert Data',
                    'data' => $data_upload_kunci
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => 500,
                    'message' => 'Gagal Insert Data'
                ], self::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Gagal Upload Soal'
            ], self::HTTP_BAD_REQUEST);
        }
    }


    public function juriMaster_post()
    {
        $param = $this->post();
        $ID_PESERTA = $param['ID_PESERTA'];
        $NAME_JURI = $param['NAME_JURI'];
        $MATA_LOMBA = $param['MATA_LOMBA'];
        $P1 = $param['P1'];
        $P2 = $param['P2'];
        $P3 = $param['P3'];
        $P4 = $param['P4'];
        $P5 = $param['P5'];
        $P6 = $param['P6'];
        $P7 = $param['P7'];
        $P8 = $param['P8'];
        $P9 = $param['P9'];
        $P10 = $param['P10'];
        $P11 = $param['P11'];
        $P12 = $param['P12'];
        $P13 = $param['P13'];
        $P14 = $param['P14'];
        $P15 = $param['P15'];
        $P16 = $param['P16'];
        $P17 = $param['P17'];
        $P18 = $param['P18'];
        $P19 = $param['P19'];
        $P20 = $param['P20'];
        $P21 = $param['P21'];
        $P22 = $param['P22'];
        $P23 = $param['P23'];
        $P24 = $param['P24'];
        $P25 = $param['P25'];
        $P26 = $param['P26'];

        $data_mst_juri = [
            'ID_PESERTA' => $ID_PESERTA,
            'NAME_JURI' => $NAME_JURI,
            'MATA_LOMBA' => $MATA_LOMBA,
            'P1' => $P1,
            'P2' => $P2,
            'P3' => $P3,
            'P4' => $P4,
            'P5' => $P5,
            'P6' => $P6,
            'P7' => $P7,
            'P8' => $P8,
            'P9' => $P9,
            'P10' => $P10,
            'P11' => $P11,
            'P12' => $P12,
            'P13' => $P13,
            'P14' => $P14,
            'P15' => $P15,
            'P16' => $P16,
            'P17' => $P17,
            'P18' => $P18,
            'P19' => $P19,
            'P20' => $P20,
            'P21' => $P21,
            'P22' => $P22,
            'P23' => $P23,
            'P24' => $P24,
            'P25' => $P25,
            'P26' => $P26,
        ];

        $posting_juri = $this->api->insert_data('juri_mst', $data_mst_juri);
        if ($posting_juri) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Insert Data',
                'data' => $posting_juri
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Gagal Insert Data'
            ], self::HTTP_BAD_REQUEST);
        }

    }

    public function registerPeserta_post()
    {
        $param = $this->post();
        $NAMA_REGU = $param['NAMA_REGU'];
        $NAMA_PANGKALAN = $param['NAMA_PANGKALAN'];
        $CATEGORY = $param['CATEGORY'];
        $SURAT_TUGAS_SEKOLAH = $param['SURAT_TUGAS_SEKOLAH'];
        $KWARRAN = $param['KWARRAN'];
        $SURAT_TUGAS_KWARRAN = $param['SURAT_TUGAS_KWARRAN'];
        $NOMOR_WHATSAPP = $param['NOMOR_WHATSAPP'];
        $NAMA_PENDAMPING = $param['NAMA_PENDAMPING'];
        $FOTO_REGU = $param['FOTO_REGU'];

        $data_register = [
            'NAMA_REGU' => $NAMA_REGU,
            'NAMA_PANGKALAN' => $NAMA_PANGKALAN,
            'CATEGORY' => $CATEGORY,
            'SURAT_TUGAS_SEKOLAH' => $SURAT_TUGAS_SEKOLAH,
            'KWARRAN' => $KWARRAN,
            'SURAT_TUGAS_KWARRAN' => $SURAT_TUGAS_KWARRAN,
            'NOMOR_WHATSAPP' => $NOMOR_WHATSAPP,
            'NAMA_PENDAMPING' => $NAMA_PENDAMPING,
            'FOTO_REGU' => $FOTO_REGU,
        ];

        $data_peserta = $this->api->insert_data('daftar_peserta', $data_register);
        if ($data_peserta) {
            $this->response([
                'status' => 200,
                'message' => 'Berhasil Insert Data',
                'data' => $data_peserta
            ], self::HTTP_OK);
        } else {
            $this->response([
                'status' => 500,
                'message' => 'Gagal Insert Data'
            ], self::HTTP_BAD_REQUEST);
        }

    }

    public function submitPupukRecap_post()
    {
        $param = $this->post();

    }












}