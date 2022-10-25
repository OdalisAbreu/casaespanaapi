<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Partners;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use mysqli;

use function PHPUnit\Framework\isEmpty;

class PartnersController extends Controller
{
    public function index()
    {
        echo 'entro';
        // $data = DB::select("DECLARE @par_existe Varchar(10) Exec Existe_Preinvitacion 28918,23799,'2022-08-17 15:00:00','ACTIVIDAD','22400274092','',0,0,1,@par_existe OUTPUT SELECT @par_existe AS 'result';");
       // return $data[0];
    }

    public function store(Request $request)
    {
        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        //---------------------Convierte la fecha al formato de consulta --------------------------------
        date_default_timezone_set('America/La_Paz');
        $originalDate = str_replace("/","-",$request->date);
        $formatDate = strtotime($originalDate);
        $initDate = date("Y-m-d 01:00:00",$formatDate);
        $initActiviti = date("Y-m-d 01:00:00",$formatDate);
        $endDate = date("Y-m-d 23:59:00",$formatDate);
        $today = strtotime(date("Y-m-d 01:00:00",time()));
        if (strtotime($initDate) == $today){
            $initActiviti = date("Y-m-d H:i:s",time());
            $newDate = strtotime ( '+2 hour' , strtotime ($initActiviti) ) ; 
            $initActiviti = date('Y-m-d H:i:s', $newDate); 
        }
        if (strtotime($initDate) < $today){
            return [
                "message"=> "La fecha de visita debe de ser mayor fecha actual",
                "success"=> false
            ];
        }
        //----------------------------------------------------------------------------------------------
        try{
            $data = DB::select("SET NOCOUNT ON; DECLARE	@par_codigo_preinvitacion numeric(12, 0)
                                  EXEC	INSERTAR_PREINVITACION
                                          @par_codigo_entidad = ".$request->codigoEntidad.",
                                          @par_codigo_familia = ".$request->codigoFamilia.",
                                          @par_codigo_miembro = ".$request->codigoMiembroFamilia.",
                                          @par_fecha_efectiva = N'".$initDate."',
                                          @par_hora_inicio = N'".$initActiviti."',
                                          @par_hora_termino = N'".$endDate."',
                                          @par_tipo_invitacion = N'INSTALACIONES',
                                          @par_primer_nombre = N'".$request->guestName."',
                                          @par_segundo_nombre = NULL,
                                          @par_primer_apellido = N'".$request->guestLastName."',
                                          @par_segundo_apellido = NULL,
                                          @par_sexo = N'".$request->gender."',
                                          @par_cedula = N'".$request->id."',
                                          @par_pasaporte = N'".$request->passport."',
                                          @par_email = N'".$request->email."',
                                          @par_telefono = NULL,
                                          @par_codigo_tercero = NULL,
                                          @par_menor_sin_acompanante = NULL,
                                          @par_codigo_destino_invitacion = 1,
                                          @par_codigo_actividad = 1,
                                          @par_cantidad_invitado_adulto = ".$request->adultGuests.",
                                          @par_cantidad_invitado_menor = ".$request->underageGuests.",
                                          @par_user_id = N'".$request->userId."',
                                          @par_codigo_preinvitacion = @par_codigo_preinvitacion OUTPUT
                                  
                                  SELECT	@par_codigo_preinvitacion as 'confirmationNumber'");
              return [
                "confirmationNumber"=> $data[0]->confirmationNumber,
                "success"=> true
              ];
        }catch(QueryException $e){
            //Valida si la razon es la cantidad de invitados
            $data = DB::select("DECLARE	@par_success varchar(10),
                                        @par_informacion varchar(4000)
    
                                EXEC	VALIDAR_CANTIDAD_INVITADOS
                                        @par_codigo_pre_invitacion = NULL,
                                        @par_codigo_entidad = ".$request->codigoEntidad.",
                                        @par_codigo_familia = ".$request->codigoFamilia.",
                                        @par_fecha_referencia = N'".$initDate."',
                                        @par_tipo_invitacion = N'INSTALACIONES',
                                        @par_cantidad_invitados = ".$request->adultGuests.",
                                        @par_tipo_validacion = 'TIPO_MIEMBRO',
                                        @par_success = @par_success OUTPUT,
                                        @par_informacion = @par_informacion OUTPUT
    
                                SELECT	@par_success as 'success',
                                        @par_informacion as 'message'");
           //return $data[0]->message;
           if($data[0]->success == true){
            return [
                "message"=> "Uno o más parámetros no contienen el formato correcto, favor validar",
                "success"=> false
            ];
           }else{
               $message = str_replace("\r"," ",$data[0]->message);
               return [
               "message"=> $message,
               "success"=> $data[0]->success
           ];
          }
        }
      
    }

    public function show($id)
    {
        $data = DB::select('Execute sp_buscar_socio '.$id);
        return $data;
    }

    public function destroy(Partners $partners)
    {
        //
    }
    public function existePreinvitacion(Request $request)
    {
        $data = DB::select("DECLARE @par_existe Varchar(10) Exec Existe_Preinvitacion 28918,23799,'2022-08-17 15:00:00','ACTIVIDAD','22400274092','',0,0,1,@par_existe OUTPUT SELECT @par_existe AS 'result';");
        return $data[0];
    }

    public function partners(Request $request)
    {
        $id =strrev($request->id);
        $token =strrev($request->token);
        $user =strrev($request->user);
        $password =strrev($request->password);
        $userToken = $token.';'.$id.';'.$password.';'.$user;
        $baseToken = base64_encode($userToken); //Base64 para el Body
        $stringToken = $request->id.':'.$request->token;
        $authToken = base64_encode($stringToken); // Base64 de Authorization

      /*  return $request->id.' - '.$request->token;

        $response = Http::withBasicAuth($request->id,$request->token)->accept('text/plain')->post('https://ov.casadeespana.com.do:9191/OV_Administracion_Socios/webAPI/webUserAdmSocios/autenticarUsuario',[$baseToken ] );
        return $response;*/

        $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ov.casadeespana.com.do:9191/OV_Administracion_Socios/webAPI/webUserAdmSocios/autenticarUsuario',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$baseToken,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic '.$authToken,
                'Content-Type: text/plain'
            ),
            ));

            $response = curl_exec($curl);

            if(curl_errno($curl)) echo curl_error($curl);
            else $array = json_decode($response);
            curl_close($curl);

            if (!isset($array->segundoNombre) ) {
               $segundoNombre =  "";
            }else{
                $segundoNombre =  $array->segundoNombre;
            }
            if (!isset($array->segundoApellido) ) {
                $segundoApellido =  "";
            }else{
                $segundoApellido =  $array->segundoApellido;
            }

            if ($array->success == 1){
                return [
                    "codigoSocio"=> $array->codigoSocio,
                    "email"=> $array->email,
                    "miembroEsCabezaFamilia"=> $array->miembroEsCabezaFamilia,
                    "miembroFamilia"=> $array->miembroFamilia,
                    "primerApellido"=> $array->primerApellido,
                    "primerNombre"=> $array->primerNombre,
                    //"segundoApellido"=> $array->segundoApellido,
                    //"segundoNombre"=> $array->segundoNombre,
                    "segundoApellido"=> $segundoApellido,
                    "segundoNombre"=> $segundoNombre,
                    "user_id"=> $request->user,
                    "codigoEntidad"=> $array->accesoMiembroFamilia->accesoWebMiembroFamiliaId->codigoEntidad,
                    "codigoFamilia"=> $array->accesoMiembroFamilia->accesoWebMiembroFamiliaId->codigoFamilia,
                    "codigoMiembroFamilia"=> $array->codigoMiembroFamilia,
                    "numeroCarnet"=> $array->numeroCarnet,
                    "sessiontoken"=> "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw==",//Este se va a generar en base a las credenciales que le pasen
                    "success"=>  True
    
                ];
            }else{
                return [
                    "message"=> $array->mensaje,
                    "success"=> $array->success
                ];
            }
      
    }
    
    public function accountreport(Request $request){
        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        $date = Carbon::now();
        $today = $date->format('d-m-Y');
        $initDate = $date->subMonths(5)->format('d-m-Y');
            $data = DB::select("DECLARE	@return_value int
                                   EXEC	@return_value = HISTORIAL_TRANS_SOCIO_APP_CE
                                        @par_codigo_socio = ".$request->codigoSocio.",
                                        @par_fecha_inicial = N'".$initDate."',
                                        @par_fecha_final = N'".$today."'
                                
                                    SELECT	'Return Value' = @return_value
                                ");

            if(!empty($data)){
                return [ 
                  "data"=>  $data,
                  "success"=> true
                ];
            }else{
                return [
                    "message"=> "Código de socio invalido",
                    "success"=> false
                ];
            }

    }
    public function invitationHistory(Request $request){

        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        $data = DB::select("PREINV_ABIERTA_SOCIO_APP_CE @par_codigo_socio = ".$request->codigoSocio);
        if(empty($data)){
            return [
                "message"=> "No tiene Invitaciones registradas",
                "success"=> false
            ];
        }
        if($request->consulta == 'all'){
            return [ 
                "data"=>  $data,
                "success"=> true
            ];
        }
        if($request->consulta == 'old'){
            date_default_timezone_set('America/La_Paz');
            $newdata = [];
            $contador = 0;
           foreach($data as $array){
            $initDate = strtotime($array->FECHA_EFECTIVA);
            $today = strtotime('-24 hour' ,strtotime(date("Y-m-d 00:00:00",time())));
            if($initDate < $today){
               $newdata[$contador] =  $array;
               $contador ++;
            }
            
           }

            return [ 
                "data"=>  $newdata,
                "success"=> true
            ];
        }

        if($request->consulta == 'new'){
            date_default_timezone_set('America/La_Paz');
            $newdata = [];
            $contador = 0;
           foreach($data as $array){

            $initDate = strtotime($array->FECHA_EFECTIVA);
            $today = strtotime('-24 hour' ,strtotime(date("Y-m-d 00:00:00",time())));
            if($initDate >= $today){
               $newdata[$contador] =  $array;
               $contador ++;
            }
            
           }

            return [ 
                "data"=>  $newdata,
                "success"=> true
            ];
        }
    }

    public function deleteInvitation(Request $request){
        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        try{
            $data = DB::select("SET NOCOUNT ON; DECLARE	@par_success varchar(10)
                                EXEC	BORRAR_PREINVITACION
                                        @par_codigo_preinvitacion = ".$request->codigoInvitacion.",
                                        @par_user_id = N'".$request->userId."',
                                        @par_success = @par_success OUTPUT
                                SELECT	@par_success as N'result'");

                                

        return [
            "message"=> "Invitación eliminada de manera correcta",
            "success"=> true
        ];

        }catch(QueryException $e){
            return [
                "message"=> "Pre-Invitaciones anteriores al día de hoy no pueden ser borradas",
                "success"=> false
            ];
       }
    }
    public function updateinvitation(Request $request)
    {
        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        //---------------------Convierte la fecha al formato de consulta --------------------------------
        date_default_timezone_set('America/Los_Angeles');
        $originalDate = str_replace("/","-",$request->date);
        $formatDate = strtotime($originalDate);
        $initDate = date("Y-m-d 01:00:00",$formatDate);
        $endDate = date("Y-m-d 23:59:00",$formatDate);
        $today = strtotime(date("d-m-Y H:i:00",time()));
        if ($today > $formatDate ){
            return [
                "message"=> "La fecha de visita debe de ser mayor fecha actual",
                "success"=> false
            ];
        }
        //----------------------------------------------------------------------------------------------
        try{
            $data = DB::select("SET NOCOUNT ON; DECLARE	@par_success varchar(10)
                                  EXEC	ACTUALIZAR_PREINVITACION
                                          @par_codigo_preinvitacion =".$request->confirmationNumber.",
                                          @par_codigo_entidad = ".$request->codigoEntidad.",
                                          @par_codigo_familia = ".$request->codigoFamilia.",
                                          @par_codigo_miembro = ".$request->codigoMiembroFamilia.",
                                          @par_fecha_efectiva = N'".$initDate."',
                                          @par_hora_inicio = N'".$initDate."',
                                          @par_hora_termino = N'".$endDate."',
                                          @par_tipo_invitacion = N'INSTALACIONES',
                                          @par_primer_nombre = N'".$request->guestName."',
                                          @par_segundo_nombre = NULL,
                                          @par_primer_apellido = N'".$request->guestLastName."',
                                          @par_segundo_apellido = NULL,
                                          @par_sexo = N'".$request->gender."',
                                          @par_cedula = N'".$request->id."',
                                          @par_pasaporte = N'".$request->passport."',
                                          @par_email = N'".$request->email."',
                                          @par_telefono = NULL,
                                          @par_codigo_tercero = NULL,
                                          @par_menor_sin_acompanante = NULL,
                                          @par_codigo_destino_invitacion = 1,
                                          @par_codigo_actividad = 1,
                                          @par_cantidad_invitado_adulto = ".$request->adultGuests.",
                                          @par_cantidad_invitado_menor = ".$request->underageGuests.",
                                          @par_user_id = N'".$request->userId."',
                                          @par_success = @par_success OUTPUT
                                  
                                  SELECT	@par_success as 'status'");
              return [
                "status"=> 'Actualizado correctamente',
                "success"=> true
              ];
        }catch(QueryException $e){
            //Valida si la razon es la cantidad de invitados
            $data = DB::select("DECLARE	@par_success varchar(10),
                                        @par_informacion varchar(4000)
    
                                EXEC	VALIDAR_CANTIDAD_INVITADOS
                                        @par_codigo_pre_invitacion = NULL,
                                        @par_codigo_entidad = ".$request->codigoEntidad.",
                                        @par_codigo_familia = ".$request->codigoFamilia.",
                                        @par_fecha_referencia = N'".$initDate."',
                                        @par_tipo_invitacion = N'INSTALACIONES',
                                        @par_cantidad_invitados = ".$request->adultGuests.",
                                        @par_tipo_validacion = 'TIPO_MIEMBRO',
                                        @par_success = @par_success OUTPUT,
                                        @par_informacion = @par_informacion OUTPUT
    
                                SELECT	@par_success as 'success',
                                        @par_informacion as 'message'");
           //return $data[0]->message;
           if($data[0]->success == true){
            return [
                "message"=> "Uno o más parámetros no contienen el formato correcto, favor validar",
                "success"=> false
            ];
           }else{
               $message = str_replace("\r"," ",$data[0]->message);
               return [
               "message"=> $message,
               "success"=> $data[0]->success
           ];
          }
        }
      
    }
    public function partnervisits(Request $request){
        //------------------valida el Token-------------------
        if($request->sessionToken != "ODNEQ0I1ODMzRkE1ODQzOzUxNzE7I0AkQDg3NjU0MzIxYU87YWlqZW11ZXJiYXNpbGFkbw=="){
            return [
                "message"=> "Token Invalido",
                "success"=> false
            ];    
        }
        //--------------------------------------------------------
        $date = Carbon::now();
        $today = $date->format('d-m-Y');
        $initDate = $date->subMonths(5)->format('d-m-Y');
            $data = DB::select("DECLARE	@return_value int
                                   EXEC	@return_value = ASISTENCIA_SOCIO_APP_CE
                                        @par_codigo_socio = ".$request->codigoSocio.",
                                        @par_fecha_inicial = N'".$initDate."',
                                        @par_fecha_final = N'".$today."'
                                
                                    SELECT	'Return Value' = @return_value
                                ");

            if(!empty($data)){
                return [ 
                  "data"=>  $data,
                  "success"=> true
                ];
            }else{
                return [
                    "message"=> "Código de socio invalido",
                    "success"=> false
                ];
            }

    }
}
