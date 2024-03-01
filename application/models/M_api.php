<?php

class M_api extends CI_Model
{
    public function get_all($table)
    {
        return $this->db->get($table)->result_array();
    }

    public function select_where($table, $where)
    {
        return $this->db->get_where($table, $where)->result_array();
    }

    public function insert_data($table, $data)
    {
        $this->db->insert($table, $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function update_data($table, $set, $where)
    {
        $this->db->from($table)
            ->where($where)
            ->set($set)
            ->update();
        return $this->db->affected_rows();
    }


    public function question_pupuk($pin)
    {
        // Mengambil nilai page dan perPage dari URL
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $perPage = $this->input->get('perPage') ? $this->input->get('perPage') : 2;

        // Menghitung offset berdasarkan halaman dan jumlah per halaman
        $offset = ($page - 1) * $perPage;

        // Ambil pertanyaan terlebih dahulu
        $this->db->select('ID_QUESTION, QUESTION_TEXT');
        $this->db->from('question');
        $this->db->limit($perPage, $offset);
        $questionQuery = $this->db->get();
        $questions = $questionQuery->result();

        // Ambil opsi untuk pertanyaan yang diambil
        $result = array();
        foreach ($questions as $question) {
            $this->db->select('ID_OPTIONS, OPTIONS_TEXT');
            $this->db->from('options');
            $this->db->where('ID_QUESTION', $question->ID_QUESTION);
            $optionsQuery = $this->db->get();
            $options = $optionsQuery->result();

            // Membuat array untuk pertanyaan beserta opsi
            $result[] = array(
                'QUEST_ID' => $question->ID_QUESTION,
                'QUESTION_TEXT' => $question->QUESTION_TEXT,
                'OPTIONS' => $options,
            );
        }
        return array(
            'status' => true,
            'message' => 'Berhasil Mendapatkan Data',
            'pages' => $page,
            'data' => $result,
        );
    }

    public function question_sandi($page, $pin)
    {
        // Mengambil nilai page dan perPage dari URL
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $perPage = $this->input->get('perPage') ? $this->input->get('perPage') : 1;

        // Menghitung offset berdasarkan halaman dan jumlah per halaman
        $offset = ($page - 1) * $perPage;

        // Ambil pertanyaan terlebih dahulu
        $this->db->select('ID_QUESTION, QUESTION_TEXT');
        $this->db->from('question');
        $this->db->limit($perPage, $offset);
        $questionQuery = $this->db->get();
        $questions = $questionQuery->result();

        // Ambil opsi untuk pertanyaan yang diambil
        $result = array();
        foreach ($questions as $question) {
            $this->db->select('ID_OPTIONS, OPTIONS_TEXT');
            $this->db->from('options');
            $this->db->where('ID_QUESTION', $question->ID_QUESTION);
            $optionsQuery = $this->db->get();
            $options = $optionsQuery->result();

            // Membuat array untuk pertanyaan beserta opsi
            $result[] = array(
                'QUEST_ID' => $question->ID_QUESTION,
                'QUESTION_TEXT' => $question->QUESTION_TEXT,
                'OPTIONS' => $options,
            );
        }

        return array(
            'status' => true,
            'message' => 'Berhasil Mendapatkan Data',
            'pages' => $page,
            'data' => $result,
        );
    }

    public function checking_user($username, $password)
    {
        $this->db->select('USERNAME, PASSWORD, ID');
        $this->db->from('user');

        $conditions = array();

        if ($username) {
            $conditions['USERNAME'] = $username;
        }

        if ($password) {
            $conditions['PASSWORD'] = $password;
        }

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        return $this->db->get()->row(); // Use row() instead of result_array()
    }

    public function checking_pin($pin)
    {
        $this->db->select('SESSION_NAME');
        $this->db->from('session');

        $conditions = array();

        if ($pin) {
            $conditions['SESSION_PIN'] = $pin;
        }

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        return $this->db->get()->row(); // Use row() instead of result_array()
    }

    public function getting_user($username, $password)
    {
        $this->db->select('ID as id_user, NAME');
        $this->db->from('user');
        // $this->db->join('session', 'session');
        return $this->db->get()->row();
    }

    public function post_jawaban_pupuk($table, $data, $where)
    {
        $this->db->insert_ignore($table, $data);
        return $this->db->affected_rows();
    }







}