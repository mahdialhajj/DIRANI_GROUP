<?php
// mobile_redirect.php - Add this to your web project
class MobileLoginRedirect {
    private $appScheme = 'companyapp://';
    
    public function generateMobileLoginUrl($companyId = null) {
        $params = [];
        if ($companyId) $params['company_id'] = $companyId;
        
        $queryString = http_build_query($params);
        return $this->appScheme . 'login' . ($queryString ? '?' . $queryString : '');
    }
    
    public function renderMobileLoginButton($companyId = null) {
        $mobileUrl = $this->generateMobileLoginUrl($companyId);
        $webUrl = "login.php" . ($companyId ? "?company_id=$companyId" : "");
        
        return "
        <a href='#' onclick='redirectToMobileApp(\"$mobileUrl\", \"$webUrl\")' 
           class='btn btn-mobile-login'>
            <i class='fas fa-mobile-alt'></i> Login with Mobile App
        </a>";
    }
}
?>