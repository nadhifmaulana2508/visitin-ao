<?php

switch ($action) {
    case 'ao_credit_portfolio_list':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        AuthMiddleware::require();
        (new AoCreditPortfolioController())->list($_GET);
        break;

    case 'ao_credit_portfolio_detail':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        AuthMiddleware::require();
        (new AoCreditPortfolioController())->detail($_GET);
        break;

    case 'ao_credit_portfolio_save_pipeline':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        AuthMiddleware::require();
        (new AoCreditPortfolioController())->savePipelineTarget(readJsonBody());
        break;

    case 'ao_credit_portfolio_save_activity':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        AuthMiddleware::require();
        (new AoCreditPortfolioController())->saveActivity(readJsonBody());
        break;

    default:
        return false;
}

return true;
