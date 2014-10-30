<?php

require_once "../PagSeguroLibrary/PagSeguroLibrary.php";

date_default_timezone_set('America/Sao_Paulo');

/**
 * Classe que mostra o uso da geracao de pagamentos recorrentes
 */
class CreatePreApprovalRequest
{

    public static function main()
    {
        // Create pre approval installment
        // Instantiate a new payment request
        $paymentRequest = new PagSeguroPaymentRequest();

        // Sets the currency
        $paymentRequest->setCurrency("BRL");

        /**
         * Para o exemplo ficticio o usuario informa se quer assinatura semanal - 1, mensal - 2 ou anual - 3.
         */
        $preApprovalItem = $_POST['preApproval'] | '3';

        // in future notifications.
        $paymentRequest->setReference("REF123");

        // Classe que monta a recorrencia
        $preApproval = new PagSeguroPreApproval();
        $preApproval->setCharge('manual');

        switch($preApprovalItem) {
            case '1':
                $dayOfMonth = date('d');
                if ($dayOfMonth > 28) {
                    $dayOfMonth = 1;
                }
                $value = '14.99';
                $paymentRequest->addItem('0001', 'Assinatura mensal', 1, $value);
                $preApproval->setAmountPerPayment($value)
                    ->setMaxAmountPerPeriod($value)->setPeriod('MONTHLY')->setDayOfMonth($dayOfMonth)
                    ->setName('Assinatura do plano mensal de revista')
                    ->setMaxTotalAmount($value * 12)
                    ->setDetails('Todo mês será cobrado o valor referente ao plano na data de adesão')
                    ->setInitialDate(date('Y-m-d\TH:i:s'))->setFinalDate(date('Y-m-d\TH:i:s', strtotime('+12 month')));
                break;
            case '2':
                $dayOfMonth = date('d');
                if ($dayOfMonth > 28) {
                    $dayOfMonth = 1;
                }
                $value = 6 * 12.99;
                $paymentRequest->addItem('0002', 'Assinatura semestral', 1, $value);
                $preApproval->setAmountPerPayment($value)
                    ->setMaxAmountPerPeriod($value * 4)
                    ->setMaxTotalAmount($value * 4)
                    ->setPeriod('SEMIANNUALLY')->setDayOfMonth($dayOfMonth)
                    ->setName('Assinatura do plano semestral de revista')
                    ->setDetails('A cada 6 meses será cobrado o valor referente ao plano na data de adesão')
                    ->setInitialDate(date('Y-m-d\TH:i:s'))->setFinalDate(date('Y-m-d\TH:i:s', strtotime('+24 month')));
                break;
            case '3':
                $dayOfMonth = date('d');
                $month      = date('m');
                /*
                 *  Verifica a data de cobranca que vai do dia 1 ao dia 28.
                 */
                if ($dayOfMonth > 28) {
                    $dayOfMonth = 1;
                    $month = $month + 1;
                }
                $value = 12 * 9.99;

                $paymentRequest->addItem('0003', 'Assinatura anual', 1, $value);
                $preApproval->setAmountPerPayment($value)
                    ->setMaxAmountPerPeriod($value * 2)
                    ->setMaxTotalAmount($value * 2)
                    ->setPeriod('YEARLY')->setDayOfYear($month.'-'.$dayOfMonth)
                    ->setName('Assinatura do plano anual de revista')
                    ->setDetails('Todo ano será cobrado o valor referente ao plano na data de adesão')
                    ->setInitialDate(date('Y-m-d\TH:i:s'))->setFinalDate(date('Y-m-d\TH:i:s', strtotime('+24 month')));
                break;
        }

        $preApproval->setReviewURL('http://www.google.com');

        $paymentRequest->setPreApproval($preApproval);

        // Sets your customer information.
        $paymentRequest->setSender(
            'Fulano',
            'email@gmail.com',
            '00',
            '99898988',
            'CPF',
            '000.000.000-00'
        );

        // Sets the url used by PagSeguro for redirect user after ends checkout process
        $paymentRequest->setRedirectUrl("http://www.google.com.br");

        try {
            /*
             * #### Credentials #####
             * Substitute the parameters below with your credentials (e-mail and token)
             * You can also get your credentials from a config file. See an example:
             * $credentials = PagSeguroConfig::getAccountCredentials();
             */
            $credentials = new PagSeguroAccountCredentials("your@email.com", "your_token_here");
            // Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
            $url = $paymentRequest->register($credentials);

            if ($url) {
                echo "<h2>Criando requisi&ccedil;&atilde;o de pagamento</h2>";
                echo "<p>URL do pagamento: <strong>$url</strong></p>";
                echo "<p><a title=\"URL do pagamento\" target='_blank' href=\"$url\">Ir para URL do pagamento.</a></p>";
            }
            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
        }

    }

    public static function printPaymentUrl($url)
    {
        if ($url) {
            echo "<h2>Criando requisi&ccedil;&atilde;o de pagamento</h2>";
            echo "<p>URL do pagamento: <strong>$url</strong></p>";
            echo "<p><a title=\"URL do pagamento\" href=\"$url\">Ir para URL do pagamento.</a></p>";
        }
    }
}

CreatePreApprovalRequest::main();
