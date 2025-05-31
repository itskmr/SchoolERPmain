<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Timetable_model extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->database();
        
        // Create timetable table if it doesn't exist
        if (!$this->db->table_exists('timetable')) {
            $this->create_table_if_not_exists();
        }
    }
    
    // Create the timetable table if it doesn't exist
    function create_table_if_not_exists() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `timetable` (
            `timetable_id` int(11) NOT NULL AUTO_INCREMENT,
            `class_id` int(11) NOT NULL,
            `section_id` int(11) NOT NULL,
            `subject_id` int(11) NOT NULL,
            `teacher_id` int(11) NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `start_time` time NOT NULL,
            `end_time` time NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`timetable_id`),
            KEY `class_id` (`class_id`),
            KEY `section_id` (`section_id`),
            KEY `subject_id` (`subject_id`),
            KEY `teacher_id` (`teacher_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
    
    // Add a new timetable entry
    function add_timetable($data) {
        // Check for conflicts
        if ($this->check_timetable_conflict($data)) {
            return false;
        }
        
        // Insert the data
        $this->db->insert('timetable', $data);
        return $this->db->insert_id();
    }
    
    // Update a timetable entry
    function update_timetable($timetable_id, $data) {
        // Check for conflicts (excluding the current entry)
        if ($this->check_timetable_conflict($data, $timetable_id)) {
            return false;
        }
        
        $this->db->where('timetable_id', $timetable_id);
        $this->db->update('timetable', $data);
        return true;
    }
    
    // Delete a timetable entry
    function delete_timetable($timetable_id) {
        $this->db->where('timetable_id', $timetable_id);
        $this->db->delete('timetable');
        return true;
    }
    
    // Get all timetable entries
    function get_all_timetables() {
        $this->db->order_by('class_id', 'ASC');
        $this->db->order_by('day', 'ASC');
        $this->db->order_by('starting_time', 'ASC');
        return $this->db->get('timetable')->result_array();
    }
    
    // Get timetable entries for a specific class and section
    function get_timetable_by_class_section($class_id, $section_id) {
        $this->db->where('class_id', $class_id);
        $this->db->where('section_id', $section_id);
        $this->db->order_by('day', 'ASC');
        $this->db->order_by('starting_time', 'ASC');
        return $this->db->get('timetable')->result_array();
    }
    
    // Get timetable entries for a specific teacher
    function get_timetable_by_teacher($teacher_id) {
        $this->db->where('teacher_id', $teacher_id);
        $this->db->order_by('day', 'ASC');
        $this->db->order_by('starting_time', 'ASC');
        return $this->db->get('timetable')->result_array();
    }
    
    // Get a specific timetable entry
    function get_timetable($timetable_id) {
        $this->db->where('timetable_id', $timetable_id);
        return $this->db->get('timetable')->row_array();
    }
        
    // Check for timetable conflicts
    public function check_timetable_conflict($data, $exclude_id = null) {
        // Check teacher conflicts without using date fields
        $this->db->where('teacher_id', $data['teacher_id']);
        if ($exclude_id) {
            $this->db->where('timetable_id !=', $exclude_id);
        }
        $teacher_conflict = $this->db->get('timetable')->num_rows() > 0;

        // Check class/section conflicts without using date fields
        $this->db->where('class_id', $data['class_id']);
        $this->db->where('section_id', $data['section_id']);
        if ($exclude_id) {
            $this->db->where('timetable_id !=', $exclude_id);
        }
        $class_conflict = $this->db->get('timetable')->num_rows() > 0;

        return $teacher_conflict || $class_conflict;
    }
    
    // Get unique time slots for a class and section
    function get_time_slots_by_class_section($class_id, $section_id) {
        $this->db->select('DISTINCT(starting_time), ending_time');
        $this->db->where('class_id', $class_id);
        $this->db->where('section_id', $section_id);
        $this->db->order_by('starting_time', 'ASC');
        return $this->db->get('timetable')->result_array();
    }
    
    // Get timetable entries for a specific day
    function get_timetable_by_day($day) {
        $this->db->where('day', $day);
        $this->db->order_by('starting_time', 'ASC');
        return $this->db->get('timetable')->result_array();
    }
    
    // Get all days that have timetable entries
    function get_all_timetable_days() {
        $this->db->select('DISTINCT(day)');
        $this->db->order_by('FIELD(day, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")', '', FALSE);
        return $this->db->get('timetable')->result_array();
    }
    
    // Get classes that have timetable entries
    function get_classes_with_timetable() {
        $this->db->select('DISTINCT(class_id)');
        $this->db->order_by('class_id', 'ASC');
        $classes = $this->db->get('timetable')->result_array();
        
        $class_names = array();
        foreach ($classes as $class) {
            $class_detail = $this->db->get_where('class', array('class_id' => $class['class_id']))->row_array();
            if ($class_detail) {
                $class_names[] = array(
                    'class_id' => $class['class_id'],
                    'name' => $class_detail['name']
                );
            }
        }
        
        return $class_names;
    }
    
    // Get teachers who have timetable entries
    function get_teachers_with_timetable() {
        $this->db->select('DISTINCT(teacher_id)');
        $this->db->order_by('teacher_id', 'ASC');
        $teachers = $this->db->get('timetable')->result_array();
        
        $teacher_names = array();
        foreach ($teachers as $teacher) {
            $teacher_detail = $this->db->get_where('teacher', array('teacher_id' => $teacher['teacher_id']))->row_array();
            if ($teacher_detail) {
                $teacher_names[] = array(
                    'teacher_id' => $teacher['teacher_id'],
                    'name' => $teacher_detail['name']
                );
            }
        }
        
        return $teacher_names;
    }
    
    public function get_timetable_events($class_id = null) {
        $this->db->select('t.*, c.name as class_name, s.name as section_name, 
                          sub.name as subject_name, tea.name as teacher_name');
        $this->db->from('timetable t');
        $this->db->join('class c', 'c.class_id = t.class_id');
        $this->db->join('section s', 's.section_id = t.section_id');
        $this->db->join('subject sub', 'sub.subject_id = t.subject_id');
        $this->db->join('teacher tea', 'tea.teacher_id = t.teacher_id');
        
        if ($class_id) {
            $this->db->where('t.class_id', $class_id);
        }
        
        return $this->db->get()->result_array();
    }
    
    public function save_timetable($data) {
        try {
            // Log incoming data
            error_log('Starting save_timetable with data: ' . json_encode($data));
            
            // Validate data
            $required_fields = ['class_id', 'section_id', 'subject_id', 'teacher_id', 
                              'start_date', 'end_date', 'start_time', 'end_time'];
            
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    error_log('Validation failed: ' . $field . ' is required');
                    return ['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }

            // Validate dates
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                error_log('Validation failed: End date cannot be before start date');
                return ['status' => 'error', 'message' => 'End date cannot be before start date'];
            }

            // Validate times
            $start_time = strtotime($data['start_time']);
            $end_time = strtotime($data['end_time']);
            if ($end_time <= $start_time) {
                error_log('Validation failed: End time must be after start time');
                return ['status' => 'error', 'message' => 'End time must be after start time'];
            }

            // Check if class exists
            $class = $this->db->get_where('class', ['class_id' => $data['class_id']])->row();
            if (!$class) {
                error_log('Validation failed: Selected class does not exist');
                return ['status' => 'error', 'message' => 'Selected class does not exist'];
            }

            // Check if section exists
            $section = $this->db->get_where('section', ['section_id' => $data['section_id']])->row();
            if (!$section) {
                error_log('Validation failed: Selected section does not exist');
                return ['status' => 'error', 'message' => 'Selected section does not exist'];
            }

            // Check if subject exists
            $subject = $this->db->get_where('subject', ['subject_id' => $data['subject_id']])->row();
            if (!$subject) {
                error_log('Validation failed: Selected subject does not exist');
                return ['status' => 'error', 'message' => 'Selected subject does not exist'];
            }

            // Check if teacher exists
            $teacher = $this->db->get_where('teacher', ['teacher_id' => $data['teacher_id']])->row();
            if (!$teacher) {
                error_log('Validation failed: Selected teacher does not exist');
                return ['status' => 'error', 'message' => 'Selected teacher does not exist'];
            }

            // Check for conflicts - Note: We use timetable_id field for the database query
            // but we're not using start_date in the WHERE clause to avoid the error
            $conflict_check = [
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'teacher_id' => $data['teacher_id']
            ];

            $exclude_id = isset($data['timetable_id']) ? $data['timetable_id'] : null;
            
            // Modified conflict check to not use start_date or end_date in WHERE clause
            $this->db->where('class_id', $data['class_id']);
            $this->db->where('section_id', $data['section_id']);
            
            // If editing, exclude current record
            if ($exclude_id) {
                $this->db->where('timetable_id !=', $exclude_id);
            }
            
            // Count conflicts
            $conflict_count = $this->db->get('timetable')->num_rows();
            
            if ($conflict_count > 0) {
                error_log('Validation failed: There is a scheduling conflict');
                return ['status' => 'error', 'message' => 'There is a scheduling conflict with another class or teacher'];
            }

            // Start transaction
            $this->db->trans_start();

            // Update or insert
            if (isset($data['timetable_id'])) {
                $this->db->where('timetable_id', $data['timetable_id']);
                $this->db->update('timetable', $data);
                $message = 'Timetable entry updated successfully';
                error_log('Updating timetable entry: ' . $data['timetable_id']);
            } else {
                $this->db->insert('timetable', $data);
                $message = 'Timetable entry added successfully';
                error_log('Inserted new timetable entry: ' . $this->db->insert_id());
            }

            // Complete transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                error_log('Database transaction failed');
                return ['status' => 'error', 'message' => 'Database transaction failed'];
            }

            error_log('Timetable saved successfully');
            return ['status' => 'success', 'message' => $message];
            
        } catch (Exception $e) {
            error_log('Error in save_timetable: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while saving the timetable: ' . $e->getMessage()];
        }
    }
    
    // Format time to 12-hour format with AM/PM
    public function format_time($time) {
        if (empty($time)) return '';
        
        $time_parts = explode(':', $time);
        if (count($time_parts) < 2) return $time;
        
        $hour = intval($time_parts[0]);
        $minutes = $time_parts[1];
        $ampm = ($hour >= 12) ? 'PM' : 'AM';
        $hour = ($hour % 12) ?: 12;
        
        return $hour . ':' . $minutes . ' ' . $ampm;
    }
    
    // Get timetable data for a specific class and section
    public function get_class_timetable_data($class_id, $section_id) {
        $this->db->select('t.*, s.name as subject_name, tc.name as teacher_name, c.name as class_name, sc.name as section_name');
        $this->db->from('class_timetable as t');
        $this->db->join('subject as s', 's.subject_id = t.subject_id', 'left');
        $this->db->join('teacher as tc', 'tc.teacher_id = t.teacher_id', 'left');
        $this->db->join('class as c', 'c.class_id = t.class_id', 'left');
        $this->db->join('section as sc', 'sc.section_id = t.section_id', 'left');
        $this->db->where('t.class_id', $class_id);
        $this->db->where('t.section_id', $section_id);
        
        return $this->db->get()->result_array();
    }
    
    // Get timetable data for a specific teacher
    public function get_teacher_timetable_data($teacher_id) {
        $this->db->select('t.*, s.name as subject_name, tc.name as teacher_name, c.name as class_name, sc.name as section_name');
        $this->db->from('class_timetable as t');
        $this->db->join('subject as s', 's.subject_id = t.subject_id', 'left');
        $this->db->join('teacher as tc', 'tc.teacher_id = t.teacher_id', 'left');
        $this->db->join('class as c', 'c.class_id = t.class_id', 'left');
        $this->db->join('section as sc', 'sc.section_id = t.section_id', 'left');
        $this->db->where('t.teacher_id', $teacher_id);
        
        return $this->db->get()->result_array();
    }
    
    // Print timetable
    public function print_timetable($type, $id) {
        $data = array();
        $data['page_title'] = get_phrase('Timetable');
        
        if ($type == 'class') {
            $class_data = $this->db->get_where('class', array('class_id' => $id))->row();
            $section_id = $this->input->get('section_id');
            $section_data = $this->db->get_where('section', array('section_id' => $section_id))->row();
            
            $data['class_name'] = $class_data->name;
            $data['section_name'] = $section_data->name;
            $data['timetable_data'] = $this->get_class_timetable_data($id, $section_id);
        } 
        else if ($type == 'teacher') {
            $teacher_data = $this->db->get_where('teacher', array('teacher_id' => $id))->row();
            $data['teacher_name'] = $teacher_data->name;
            $data['timetable_data'] = $this->get_teacher_timetable_data($id);
        }
        
        $this->load->view('backend/timetable_print_view', $data);
    }
} 