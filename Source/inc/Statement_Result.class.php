<?

class Statement_Result
{
        private $_bindVarsArray = array();
        private $_results = array();

        public function __construct(&$stmt)
        {
                $meta = $stmt->result_metadata();

                while ($columnName = $meta->fetch_field())
                        $this->_bindVarsArray[] = &$this->_results[$columnName->name];

                call_user_func_array(array($stmt, 'bind_result'), $this->_bindVarsArray);

                $meta->close();
        }

        public function Get_Array()
        {
                return $this->_results;
        }

        public function Get($column_name)
        {
                return $this->_results[$column_name];
        }
}

