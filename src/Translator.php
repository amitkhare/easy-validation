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

class Translator {
    private static $localePath = __DIR__."/locales/";
    private static $locale = "en-IN";
    public static function translate($keyString,$fields=null,$locale=null,$localePath=null){
        
        if($localePath){
            self::$localePath = $localePath;
        }
        
        if($locale){
            self::$locale = $locale;
        }
        
        $string = self::getString(strtoupper($keyString));
        
        if($fields==null){
            return $string;
        }
        
        if(is_array($fields)){
            foreach ($fields as $key=>$field) {
                $fields[$key] = self::formatField($field);
            }
        } else {
            $fields = [self::formatField($fields)];
        }
        
        return vsprintf($string, $fields);
        
    }
    
    private static function getString($keyString){
        
        if(!$val = self::getVal(self::$localePath.self::$locale.".lang",$keyString)){

            // search locale in internal directory
            if(!$val = self::getVal(__DIR__."/locales/".self::$locale.".lang",$keyString)){
                
                // search default locale in internal directory
                if(!$val = self::getVal(__DIR__."/locales/en-IN.lang",$keyString)){
                    
                    // noting found return formatted keystring
                    return self::formatString($keyString);
                    
                }
                
            }
            
        }
        // return fromatted found value;
        return $val;

    }
    
    private static function getVal($file,$keyString){
        if(file_exists($file)){
           
           $file = file_get_contents($file);
           $file = json_decode($file,true);
           
           if(array_key_exists($keyString,$file)){
                return $file[$keyString];
            }
        }
    }
    
    private static function formatString($string){
        $string = str_replace("_"," ",$string);
        $string = str_replace("-"," ",$string);
        $string = strtolower($string);
        $string = ucwords($string);
        return $string;
    }
    
    private static function formatField($field) {
        $field = strtoupper($field);
        return self::getString($field);
    }
     
}