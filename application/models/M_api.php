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
        $insert_query = $this->db->insert_string($table, $data);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insert_get_id($table, $data)
    {
        $insert_query = $this->db->insert_string($table, $data);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
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
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $perPage = $this->input->get('perPage') ? $this->input->get('perPage') : 1;

        $offset = ($page - 1) * $perPage;

        $this->db->select('ID_QUESTION, QUESTION_TEXT');
        $this->db->from('question');
        $this->db->limit($perPage, $offset);
        $questionQuery = $this->db->get();
        $questions = $questionQuery->result();

        $result = array();
        foreach ($questions as $question) {
            $this->db->select('ID_OPTIONS, OPTIONS_TEXT');
            $this->db->from('options');
            $this->db->where('ID_QUESTION', $question->ID_QUESTION);
            $optionsQuery = $this->db->get();
            $options = $optionsQuery->result();

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

        return $this->db->get()->row();
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


    public function nilai_sandi($username, $pin)
    {
        // Assuming you have loaded the database library in your CodeIgniter controller or model

        // $username = 'ELANG_SMPN18SEMARANG';

        $this->db->select('user.USERNAME');
        $this->db->select_sum('(CASE WHEN answer_key_sandi.K1 = responses_sandi.kata1 THEN 4 ELSE 0 END +
                        CASE WHEN answer_key_sandi.K2 = responses_sandi.kata2 THEN 4 ELSE 0 END +
                        CASE WHEN answer_key_sandi.K3 = responses_sandi.kata3 THEN 4 ELSE 0 END +
                        CASE WHEN answer_key_sandi.K4 = responses_sandi.kata4 THEN 4 ELSE 0 END +
                        CASE WHEN answer_key_sandi.K5 = responses_sandi.kata5 THEN 4 ELSE 0 END)', 'Nilai');

        $this->db->from('responses_sandi');
        $this->db->join('answer_key_sandi', 'responses_sandi.ID_QUESTION = answer_key_sandi.ID_QUESTION');
        $this->db->join('user', 'responses_sandi.ID_USER = user.ID');
        $this->db->where('user.USERNAME', $username);
        $this->db->group_by('user.USERNAME');

        $query = $this->db->get();

        // Now you can execute the query and get the result
        $result = $query->result();

        // You can then access the result as needed, for example:
        foreach ($result as $row) {
            echo 'Username: ' . $row->USERNAME . ', Nilai: ' . $row->Nilai;
        }
    }


    public function checking_panitia($username, $password)
    {
        $this->db->select('*');
        $this->db->from('account_panitia');

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

    public function select_panitia($select, $table, $table1, $on_table, $table2)
    {
        $this->db->select($select);
        $this->db->from($table);
        $this->db->join($table1, "{$on_table} = {$table2}");
        return $this->db->get()->result_array();
    }

    public function insert_pupuk($data_question, $data_options)
    {
        $this->db->trans_begin();

        $this->db->insert('question', $data_question);
        $question_id = $this->db->insert_id();

        foreach ($data_options as &$option) {
            $option['ID_QUESTION'] = $question_id;
        }

        $this->db->insert_batch('options', $data_options);


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
        return $this->db->trans_status();
    }

    public function submit_pupuk($username, $session_pin, $jenis_soal)
    {
        $this->db->select();
    }












}