<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;
use Twilio\Rest\Client;


class SendWhatsappNotifications implements Interactor
{
    public function __construct(
    ) {

    }


    public function Execute(object $data = null)
    {

        $sid    = $_ENV['TWILIO_SID'];
        $token  = $_ENV['TWILIO_TOKEN'];
        $twilio = new Client($sid, $token);
        
        $message = $twilio->messages->create(
            "whatsapp:+31651359308", // to
        array(
            "from" => "whatsapp:+14155238886",  
            "body" => "
            Fluiten SKC HS 3 - Aspasia HS 2 (21:00 aanwezig). For the English version, scroll down.

            Beste Selma de Ruijter,
            
            Aanstaande 22 september 2023 moet je een wedstrijd fluiten om 21:30. Je zult de wedstrijd SKC HS 3 - Aspasia HS 2 gaan fluiten.
            
            Extra informatie over scheidsen vind je door op onderstaande links te klikken:
            
            Het Scheidsrechters Hulpdocument ‘Scheidsrechters cursus 2021’
            De Nevobo spelregels
            DWF instructievideo
            Met vriendelijke groet,
            
            Dominique Boormans
            
            (Dit is een automatisch gegenereerd bericht)
            
            Dear Selma de Ruijter,
            
            Upcoming 22 September 2023, you have been assigned as a referee at 21:30. You are the referee of the SKC HS 3 - Aspasia HS 2 match.
            
            Additional information about being a referee can be found by clicking on the link below. The following documents can be found:
            
            The Referee Course Manual ‘Refereecourse’
            The Nevobo game rules
            DWF instruction video
            Best Regards,
            
            Dominique Boormans"
            )
        );
    
      print($message->sid);
    
    

    }
}
