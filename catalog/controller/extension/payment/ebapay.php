<?php

class ControllerExtensionPaymentebapay extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/ebapay');
        $this->load->model('checkout/order');
        $this->load->library('encryption');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['error_warning'] = false;

        $telephone = $order_info['telephone'];
        $description = 'پرداخت سفارش شناسه ' . $order_info['order_id'];

        if (extension_loaded('curl')) {
            $parameters = [
                'gatewayKey'   => $this->config->get('payment_ebapay_api'),
                'amount'       => (int)($this->currency->format($order_info['total'], 'IRR', null, false)),
                'callback'     => $this->url->link('extension/payment/ebapay/callback', 'order_id=' .$order_info['order_id'], '', 'SSL'),
                'factorNumber' => $order_info['order_id'],
                'mobileNo'     => $telephone,
                'description'  => $description
            ];

            $ch = curl_init($this->config->get('payment_ebapay_send'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            $result = json_decode($result);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_status != 201 || empty($result) || empty($result->ResNum) || empty($result->url)) {
                // Set Order status id to 10 (Failed) and add a history.
                $msg = sprintf('خطا هنگام ایجاد تراکنش. وضعیت خطا: %s - کد خطا: %s - پیام خطا: %s', $http_status, $result->error_code, $result->error_message);
                $json['error'] = $msg;
            } else {
                $data['action'] = $result->url;
                $json['success'] = $data['action'];
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }else{
            $data['error_warning'] = $this->language->get('error_curl');
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/ebapay')) {
            return $this->load->view($this->config->get('config_template') . '/extension/payment/ebapay', $data);
        } else {
            return $this->load->view('/extension/payment/ebapay', $data);
        }
    }

    public function callback()
    {
        ob_start();

        $this->load->language('extension/payment/ebapay');
        $this->load->model('checkout/order');
        $this->load->library('encryption');

        $this->document->setTitle($this->language->get('heading_title'));

        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : false;

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $data['heading_title'] = $this->language->get('heading_title');

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home', '', 'SSL');

        $data['error_warning'] = false;

        $data['continue'] = $this->url->link('checkout/cart', '', 'SSL');


        if($this->request->get['Status'] == 'OK'){
            $status = $this->request->get['Status'];
            $resNum = $this->request->get['ResNum'];
            $orderId = $this->request->get['amp;order_id'];

            //Call verify API
            $amount = $this->currency->format($order_info['total'], 'IRR', null, false);
            $parameters = [
                'ResNum' =>  $resNum,
                'amount' => $amount,
            ];
           
            $ch = curl_init($this->config->get('payment_ebapay_verify'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            $result = json_decode($result);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result->status != 200) {
                $code = isset($result->status) ? $result->errorCode : 'Undefined';
                            $data['error_warning'] = $this->language->get('error_request') . '<br/><br/>' . $this->language->get('error_code') . $code;	
                $this->log->write('errorrrrrrrrrr');
                $this->log->write($data['error_warning']);
                } 
            else {
                $this->log->write('suscesssss');
                $refNum = $result->RefNum;
                $comment = $this->language->get('text_transaction') . $refNum;
                            $this->log->write($comment);
                $this->model_checkout_order->addOrderHistory($orderId, $this->config->get('payment_ebapay_order_status_id'), $comment);
                

            }
        }
        else {
            $data['error_warning'] = $this->language->get('error_data');
        }


        if ($data['error_warning']) {

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_basket'),
                'href'      => $this->url->link('checkout/cart', '', 'SSL'),
                'separator' => ' » '
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_checkout'),
                'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
                'separator' => ' » '
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['footer'] = $this->load->controller('common/footer');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/ebapay_callback')) {

                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/ebapay_callback', $data));

            } else {

                $this->response->setOutput($this->load->view('extension/payment/ebapay_callback', $data));
            }

        } else {

            $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
        }
    }

    protected function common($url, $parameters)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

?>
