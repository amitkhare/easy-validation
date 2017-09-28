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
 
use AmitKhare\EasyTranslator;

class EasyValidation {
	private $code;
	private $msgs;
	private $source;
    private $sanitized = array();
    private $uniqueArray;
    private $dbConn;
    private $isConnected=false;
    private $localePath = __DIR__."/locales/";
	private $locale = "hi-IN";
	
	function __construct($host=null,$username=null,$password=null,$dbname=null){
	    
	    EasyTranslator::setLocalePath($this->localePath);
	    EasyTranslator::setLocale($this->locale);
	    
		$this->msgs = false;
		$this->code = 200;
        if($host!==null && $username!==null && $password!==null && $dbname!==null){
            $this->connect($host,$username,$password,$dbname);
        }
        
	}
	
	public function setLocale($locale,$localePath=null){
        $this->locale = $locale;
        EasyTranslator::setLocale($locale);
	    if($localePath){
            $this->localePath = $localePath;
            EasyTranslator::setLocalePath($localePath);
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
    
    private function connect($host="localhost",$username="root",$password="",$dbname="EasyValidationDB"){
        $mysqli = new \mysqli($host,$username,$password,$dbname);
        /* check connection */
        if (mysqli_connect_errno()) {
            $this->setStatus(500,"DB", $this->translate("DB_ERROR",mysqli_connect_error()));
            $this->isConnected=false;
        } else {
            $this->dbConn = $mysqli;
            $this->isConnected=true;
        }
    }
    
	private function translate($keyString,$fields=null) {
	    return EasyTranslator::translate($keyString,$fields);
	}
	
    public function match($field1="",$field2="",$rules=[]){
        
        if($rules){
            $this->check($field1,$rules);
            $this->check($field2,$rules);
        }
        
        if(!$this->is_set($field1) || !$this->is_set($field2)){
           return false;
        }
        
        if($this->source[$field1] != $this->source[$field2]){
            $status = $this->translate("FIELDS_DONT_MATCH",[$field1,$field2]);
            $this->setStatus(500,$field2,$status);
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
                $this->setStatus(500,$field, $this->translate("FIELD_VALUE_TOO_LONG",[$field,$max]));
                $this->sanitizeString($field);
            }
        }
        if ($min>0 && $min <= $max){
            if(strlen($this->source[$field]) < $min) {
                $this->setStatus(500,$field, $this->translate("FIELD_VALUE_TOO_SHORT",[$field,$min]));
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
	public function setStatus($code,$field,$msg){
			$this->code=$code;
			$this->msgs[$field]=$msg;
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
            $this->setStatus(500,$field, $this->translate("FIELD_NOT_SET",$field));
        }
    }
    private function required($field){
        if(!isset($this->source[$field])){
            $this->setStatus(500,$field, $this->translate("FIELD_NOT_SET",$field));
        } elseif(empty($this->source[$field]) || $this->source[$field]=="" || strlen($this->source[$field]) == 0){
            $this->setStatus(500,$field, $this->translate("FIELD_REQUIRED",$field));
        }
    }
    private function validateIpv4($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_IPV4",$field));
        }
    }
    public function validateIpv6($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_IPV6",$field));
        }
    }
    private function validateFloat($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_FLOAT) === false) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_FLOAT",$field));
        }
    }
    private function validateString($field) {
        if(isset($this->source[$field])) {
            if(!is_string($this->source[$field])) {
                $this->setStatus(500,$field, $this->translate("FIELD_INVALID_STRING",$field));
                $this->sanitizeString($field);
            }
        }
    }
    private function alphaNumeric($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("/[^a-z_\.\-0-9\s]/i", $this->source[$field] ) == true) {
                $this->setStatus(500,$field, $this->translate("FIELD_INVALID_ALPHA_NUMERIC",$field));
                $this->sanitizeString($field);
            }
        }
    }

    private function alpha($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("/[^a-z\s]/i", $this->source[$field] ) == true) {
                $this->setStatus(500,$field, $this->translate("FIELD_INVALID_ALPHA",$field));
                $this->sanitizeString($field);
            }
        }
    }
    private function alphaNumericUnicode($field,$min=0,$max=0) {
        if(isset($this->source[$field])) {
            if(preg_match("[^a-z_\.\-0-9\x{4e00}-\x{9fa5}]/ui", $this->source[$field] ) == true) {
                $this->setStatus(500,$field, $this->translate("FIELD_INVALID_ALPHA_NUMERIC_UNICODE",$field));
                $this->sanitizeString($field);
            }
        }
    }
    private function validateNumeric($field, $min=0, $max=0) {
        if(preg_match("/[^0-9]+/",$this->source[$field])) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_NUMERIC",$field));
            $this->sanitizeNumeric($field);
        }
    }
    private function validateUrl($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_URL) === FALSE) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_URL",$field));
            $this->sanitizeUrl($field);
        }
    }
    private function validateEmail($field) {
        if(filter_var($this->source[$field], FILTER_VALIDATE_EMAIL) === FALSE) {
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_EMAIL",$field));
            $this->sanitizeEmail($field);
        }
    }
    private function validateBool($field) {
        filter_var($this->source[$field], FILTER_VALIDATE_BOOLEAN);{
            $this->setStatus(500,$field, $this->translate("FIELD_INVALID_BOOLEAN",$field));
        }
    }
    private  function isUnique($field,$table,$column){
        if($this->isValid()) {
            if($this->isConnected){
                $query = mysqli_query($this->dbConn, "SELECT * FROM `$table` WHERE `$column`='".$this->sanitizeField($field)."'");
                if(mysqli_num_rows($query) > 0){
                    $this->setStatus(500,$field, $this->translate("FIELD_VALUE_ALREADY_EXISTS",$field));
                }
            } else {
                $this->setStatus(500,$field, $this->translate("DB_NOT_CONNECTED",$field));
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
