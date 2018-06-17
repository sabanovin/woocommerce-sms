<?php
/**
 * @author Ahmad Rajabi <Ahmad@rajabi.us>
 */
class sabanovin extends AktuelSms
{
    public function __construct($message, $gsmnumber)
    {
        $this->message = $this->utilmessage($message);
        $this->gsmnumber = $this->utilgsmnumber($gsmnumber);
    }
    public function send()
    {
        if ($this->gsmnumber == "numbererror") {
            $log[] = ("Number format error." . $this->gsmnumber);
            $error[] = ("Number format error." . $this->gsmnumber);
            return null;
        }
        $params   = $this->getParams();
        $username = $params->user ;
        $api      = $params->apiid ;
        $cnum     = $this->gsmnumber ;
        $mes      = $this->message ;
        $url      = "http://api.sabanovin.com/v1/";
        $result = @file_get_contents($url . $api . 
            '/sms/send.json?gateway=' . $username .
            '&to=' . $cnum . '&text=' . $mes);
        $json = json_decode($result, true);
        if($json['status']['code'] == 200){
            $log[] = ("Message sent.");
        }else {
            $log[] = ("Error.");
            $error[] = ('ERR: '.$json['status']['code']);
        }
        return array(
            'log' => $log,
            'error' => $error,
            'msgid' => $json['entries'][0]['reference_id']
        );
    }
    public function balance()
    {
        $params = $this->getParams();
        if ($params->user && $params->apiid) {
            $url = "http://api.sabanovin.com/v1/";
            $result = @file_get_contents($url . $params->apiid . "/credit.json");
            $json = json_decode($result, true);
            if ($json['status']['code'] == 200) {
                return null;
            } else {
                return $json['entry']['credit'];
            }
        } else {
            return null;
        }
    }
    public function report($msgid)
    {
        $params = $this->getParams();
        if ($params->user && $params->apiid && $msgid) {
            $url = "http://api.sabanovin.com/v1/";
            $result = @file_get_contents($url . $params->apiid . 
            '/sms/status.json?reference_id=' . $msgid);
            $json = json_decode($result, true);
            if ($json['entries'][0]['status'] == 'DELIVERED') {
                return "success";
            } else {
                return "error";
            }
        } else {
            return null;
        }
    }
    //You can spesifically convert your gsm number. See netgsm for example
    public function utilgsmnumber($number)
    {
        return $number;
    }
    //You can spesifically convert your message
    public function utilmessage($message)
    {
        return $message;
    }
}
return array(
    'value' => 'sabanovin',
    'label' => 'sabaNovin',
    'fields' => array(
        'user', 'apiid'
    )
);
