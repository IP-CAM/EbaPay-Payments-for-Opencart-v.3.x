<?php 

class ControllerExtensionPaymentebapay extends Controller
{
	private $error = array ();

	public function index()
	{
		
		$this->load->language('extension/payment/ebapay');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('payment_ebapay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension',  'user_token='.$this->session->data['user_token'] . '&type=payment', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');
        $data['text_edit'] = $this->language->get( 'text_edit' );

		$data['entry_api'] = $this->language->get('entry_api');
		$data['entry_send'] = $this->language->get('entry_send');
		$data['entry_verify'] = $this->language->get('entry_verify');
		$data['entry_gateway'] = $this->language->get('entry_gateway');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');
      	$data['tab_additional'] = $this->language->get('tab_additional');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token='. $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token='. $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/ebapay','user_token='.  $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {

			$data['action'] = $this->url->link('extension/payment/ebapay', 'user_token='. $this->session->data['user_token'], true);

		} else {

			$data['action'] = $this->url->link('extension/payment/ebapay', 'user_token='. $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='. $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->error['warning'])) {

			$data['error_warning'] = $this->error['warning'];

		} else {

			$data['error_warning'] = false;
		}

		if (isset($this->error['api'])) {

			$data['error_api'] = $this->error['api'];

		} else {

			$data['error_api'] = false;
		}

		if (isset($this->error['send'])) {

			$data['error_send'] = $this->error['send'];

		} else {

			$data['error_send'] = false;
		}

		if (isset($this->error['verify'])) {

			$data['error_verify'] = $this->error['verify'];

		} else {

			$data['error_verify'] = false;
		}

		if (isset($this->error['gateway'])) {

			$data['error_gateway'] = $this->error['gateway'];

		} else {

			$data['error_gateway'] = false;
		}

		if (isset($this->request->post['payment_ebapay_api'])) {

			$data['payment_ebapay_api'] = $this->request->post['payment_ebapay_api'];

		} else {

			$data['payment_ebapay_api'] = $this->config->get('payment_ebapay_api');
		}

		if (isset($this->request->post['payment_ebapay_send'])) {

			$data['payment_ebapay_send'] = $this->request->post['payment_ebapay_send'];

		} else {

			$data['payment_ebapay_send'] = $this->config->get('payment_ebapay_send');

			if(isset($data['payment_ebapay_send'])){

				$data['payment_ebapay_send'] = $data['payment_ebapay_send'];

			} else {

				$data['payment_ebapay_send'] = 'https://client.api.ebapay.ir/transactions/gate';
			}
		}
		
		if (isset($this->request->post['payment_ebapay_verify'])) {

			$data['payment_ebapay_verify'] = $this->request->post['payment_ebapay_verify'];

		} else {

			$data['payment_ebapay_verify'] = $this->config->get('payment_ebapay_verify');

			if(isset($data['payment_ebapay_verify'])){

				$data['payment_ebapay_verify'] = $data['payment_ebapay_verify'];

			} else {

				$data['payment_ebapay_verify'] = 'https://client.api.ebapay.ir/transactions/verification';
			}
		}

		if (isset($this->request->post['payment_ebapay_gateway'])) {

			$data['payment_ebapay_gateway'] = $this->request->post['payment_ebapay_gateway'];

		} else {

			$data['payment_ebapay_gateway'] = $this->config->get('payment_ebapay_gateway');

			if(isset($data['payment_ebapay_gateway'])){

				$data['payment_ebapay_gateway'] = $data['payment_ebapay_gateway'];

			} else {

				$data['payment_ebapay_gateway'] = 'https://ebapay.ir/pg/';
			}
		}

		if (isset($this->request->post['payment_ebapay_order_status_id'])) {

			$data['payment_ebapay_order_status_id'] = $this->request->post['payment_ebapay_order_status_id'];

		} else {

			$data['payment_ebapay_order_status_id'] = $this->config->get('payment_ebapay_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_ebapay_status'])) {

			$data['payment_ebapay_status'] = $this->request->post['payment_ebapay_status'];

		} else {

			$data['payment_ebapay_status'] = $this->config->get('payment_ebapay_status');
		}

		if (isset($this->request->post['payment_ebapay_sort_order'])) {

			$data['payment_ebapay_sort_order'] = $this->request->post['payment_ebapay_sort_order'];

		} else {

			$data['payment_ebapay_sort_order'] = $this->config->get('payment_ebapay_sort_order');
		}
		
		
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/ebapay', $data));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/ebapay')) {

			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_ebapay_api']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['api'] = $this->language->get('error_api');
		}

		if (!$this->request->post['payment_ebapay_send']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['send'] = $this->language->get('error_send');
		}

		if (!$this->request->post['payment_ebapay_verify']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['verify'] = $this->language->get('error_verify');
		}

		if (!$this->request->post['payment_ebapay_gateway']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['gateway'] = $this->language->get('error_gateway');
		}

		return !$this->error;
	}
}