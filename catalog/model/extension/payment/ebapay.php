<?php 

class ModelExtensionPaymentebapay extends Model
{
	public function getMethod($address)
	{
		$this->load->language('extension/payment/ebapay');

		if ($this->config->get('payment_ebapay_status')) {

			$status = true;

		} else {

			$status = false;
		}

		$method_data = array ();

		if ($status) {

			$method_data = array (
        		'code' => 'ebapay',
        		'title' => $this->language->get('text_title'),
				'terms' => '',
				'sort_order' => $this->config->get('payment_ebapay_sort_order')
			);
		}

		return $method_data;
	}
}