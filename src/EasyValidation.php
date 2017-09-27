<?php namespace AmitKhare;
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */
/**
 * Validbit is an easy to use PHP validation library.
 *
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link https://github.com/amitkhare/easy-validation
 * @author Amit Kumar Khare <me.amitkhare@gmail.com>
 */
class EasyValidation {
	private $code;
	private $msgs;
	private $source;
    private $sanitized = array();
    private $uniqueArray;
    private $dbConn;
    private $isConnected=false;
	function __construct($host=false,$username=false,$password=false,$dbname=false){
		$this->msgs = false;
		$this->code = 200;
        if($host!==false && $username!==false && $password!==false && $dbname!==false){
            $this->connect($host,$username,$password,$dbname);
        }
	}
	public function setSource($source){
        $this->source = $source;
    }
    private function sanitizeField($field){
        if($this->isConnected){
            $safeField = $this->dbConn->real_escape_string(trim(strip_tags($this->source[$field])));
        } else {
            $safeField = filter_var($this->source[$field], FILTER_SANITIZE_STRING);
        }
        return $safeField;
    }
    private function connect($host="localhost",$username="root",$password="",$dbname="slimtestdb"){
        $mysqli = new \mysqli($host,$username,$password,$dbname);
        /* check connection */
        if (mysqli_connect_errno()) {
            $this->setStatus(500,sprintf("Database Error: %s.", mysqli_connect_error()));
            $this->isConnected=false;
        } else {
            $this->dbConn = $mysqli;
            $this->isConnected=true;
        }
    }
    public function match($field1="",$field2="",$rules=null){
        
        if($rules){
            $this->check($field1,$rules);
            $this->check($field2,$rules);
        }
        
        if($this->source[$field1] != $this->source[$field2]){
            $this->setStatus(500,sprintf("The `%s` field doesn't match with `%s`.", str_replace("_"," ",ucfirst($field1)),str_replace("_"," ",ucfirst($field2))));
        }
        
    }
	public function check($field="",$rules="required|numeric|min:1|max:50"){
        $rules = explode("|", $rules);
        $min = $max =0;
        if($this->is_set($field)){
            foreach ($rules as $minMax) {
                if(preg_match("/min\:[0-9]+/",$minMax)){
                    $min = (explode("n:",$minMax)) ? explode("in:",$minMax)[1] : 0 ;
                }
                if(preg_match("/max\:[0-9]+/",$minMax)){
                    $max = (explode("ax:",$minMax)) ? explode("ax:",$minMax)[1] : 0 ;
                }
            }
            foreach ($rules as $uniqueField) {
                if(preg_match("/(unique\:)/", $uniqueField)){
                    if($param = preg_split("/(unique\:)/", $uniqueField)){
                        $param = $param[1];
                        $this->uniqueArray[$field]['table']  = explode(".",$param)[0];
                        $this->uniqueArray[$field]['column'] = explode(".",$param)[1];
                    }
                }
            }
            
            if($min > 0 || $max > 0){
                $this->minMax($field,$min,$max);
            }
            
            foreach ($rules as $rule) {
                $this->_fetchRule($field,$rule);
            }
        }
	}
	private function minMax($field,$min=0,$max=0){
	    if ($max>0 && $max>=$min){
            if(strlen($this->source[$field]) > $max) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is too long, it should not more than '.$max.' characters.');
                $this->sanitizeString($field);
            }
        }
        if ($min>0 && $min <= $max){
            if(strlen($this->source[$field]) < $min) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is too short, it should be at least '.$min.' characters long.');
                $this->sanitizeString($field);
            }
        }
	}
    // /min\:[0-9]+/
	private function  _fetchRule($field,$rule){
		switch($rule){
                case ( preg_match("/(unique\:)/", $rule ) == true):
                    $this->isUnique($field,$this->uniqueArray[$field]['table'],$this->uniqueArray[$field]['column']);
                    break;
                case 'required':
                    $this->required($field);
                    break;
                case 'email':
                    $this->validateEmail($field);
                    break;
                case 'url':
                    $this->validateUrl($field);
                    break;
                case 'numeric':
                    $this->validateNumeric($field);
                    break;
                case 'string':
                    $this->validateString($field);
                    break;
                case 'alpha':
                    $this->alpha($field);
                    break;
                case 'alphanum':
                    $this->alphaNumeric($field);
                    break;
                case 'alphanumUnicode':
                    $this->alphaNumericUnicode($field);
                    break;
                case 'float':
                    $this->validateFloat($field);
                    break;
                case 'ipv4':
                    $this->validateIpv4($field);
                    break;
                case 'ipv6':
                    $this->validateIpv6($field);
                    break;
                case 'bool':
                    $this->validateBool($field);
                    break;
            }
	}
	public function setStatus($code,$msg){
			$this->code=$code;
			$this->msgs[]=$msg;
	}
	public function isValid(){
		if($this->code===200){
			return true;
		}
		return false;
	}
	public function getStatus(){
        $status = array(
                "code"=>$this->code,
                "msgs"=>$this->msgs,
                "source"=>$this->source
            );
        return $status;
    }
    public function getSanitized(){
        return $this->sanitized;
    }
	static function makeStatus($code=200,$msg="ok"){
		return array("code"=>$code,"msgs"=>array($msg));
	}
	static function ifSet($data=array(),$field="",$default=0){
		if(isset($data[$field])){
			return $data[$field];
		}
		return $default;
	}
	private function is_set($field) {
        if(isset($this->source[$field])){
            return true;
        }else {
            $this->setStatus(500,sprintf("The `%s` field is not set.", str_replace("_"," ",ucfirst($field)) ));
        }
    }
    private function required($field){
        if(!isset($this->source[$field])){
            $this->setStatus(500,sprintf("The `%s` field is not set.", str_replace("_"," ",ucfirst($field))));
        } elseif(empty($this->source[$field]) || $this->source[$field]=="" || strlen($this->source[$field]) == 0){
            $this->setStatus(500,sprintf("The `%s` field is required.", str_replace("_"," ",ucfirst($field))));
        }
    }
    private function validateIpv4($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is not a valid IPv4');
        }
    }
    public function validateIpv6($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is not a valid IPv6');
        }
    }
    private function validateFloat($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_FLOAT) === false) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is an invalid float');
        }
    }
    private function validateString($field) {
        if(isset($this->source[$field])) {
            if(!is_string($this->source[$field])) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is invalid string');
                $this->sanitizeString($field);
            }
        }
    }
    private function alphaNumeric($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("/[^a-z_\.\-0-9\s]/i", $this->source[$field] ) == true) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is invalid string');
                $this->sanitizeString($field);
            }
        }
    }

    private function alpha($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("/[^a-z\s]/i", $this->source[$field] ) == true) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is invalid.');
                $this->sanitizeString($field);
            }
        }
    }
    private function alphaNumericUnicode($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("[^a-z_\.\-0-9\x{4e00}-\x{9fa5}]/ui", $this->source[$field] ) == true) {
                $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is invalid string');
                $this->sanitizeString($field);
            }
        }
    }
    private function validateNumeric($field, $min=0, $max=0) {
        if(preg_match("/[^0-9]+/",$this->source[$field])) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is an invalid number');
            $this->sanitizeNumeric($field);
        }
    }
    private function validateUrl($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_URL) === FALSE) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is not a valid URL');
            $this->sanitizeUrl($field);
        }
    }
    private function validateEmail($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_EMAIL) === FALSE) {
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is not a valid email address');
            $this->sanitizeEmail($field);
        }
    }
    private function validateBool($field) {
        filter_var($this->source[$field], FILTER_VALIDATE_BOOLEAN);{
            $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' is Invalid');
        }
    }
    private  function isUnique($field,$table,$column){
        if($this->isValid()) {
            if($this->isConnected){
                $query = mysqli_query($this->dbConn, "SELECT * FROM `$table` WHERE `$column`='".$this->sanitizeField($field)."'");
                if(mysqli_num_rows($query) > 0){
                    $this->setStatus(500,str_replace("_"," ",ucfirst($field)) . ' already exists');
                }
            } else {
                    $this->setStatus(500,' Database not connected. Database related rules will be unavailable.');
            }
        }
    }
    public function sanitizeEmail($field) {
        $email = preg_replace( '((?:\n|\r|\t|%0A|%0D|%08|%09)+)i' , '', $this->source[$field] );
        $this->sanitized[$field] = (string) filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    private function sanitizeUrl($field) {
        $this->sanitized[$field] = (string) filter_var($this->source[$field],  FILTER_SANITIZE_URL);
    }
    private function sanitizeNumeric($field) {
        $this->sanitized[$field] = (int) filter_var($this->source[$field], FILTER_SANITIZE_NUMBER_INT);
    }
    private function sanitizeString($field) {
        $this->sanitized[$field] = (string) filter_var($this->source[$field], FILTER_SANITIZE_STRING);
    }
}
