<?php
$configpay = array (
    //应用ID,您的APPID。
    'app_id' => "2017052307318743",
    //商户私钥
    'merchant_private_key' => 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==',
    //编码格式
    'charset' => "UTF-8",
    //签名方式
    'sign_type'=>"RSA2",
    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB',
);
return [
    // 郵箱配置
    'EMAIL'=>[
        'senderEmail' => 'chitushgyl@163.com',// 發送郵箱賬號 todo
        'senderEmailSMTP' => 'AESOBKBQUBFALUJQ',// SMTP密碼 todo
        'senderEmailHOST' => 'smtp.163.com',// 郵箱 host todo
         'senderEmail_Info' => 'chitushgyl@163.com',// 發送郵箱賬號 todo
        'senderEmailSMTP_Info' => 'AESOBKBQUBFALUJQ',// SMTP密碼 todo
        'senderEmailHOST_Info' => 'smtp.163.com',// 郵箱 host todo
    ],
    'configpay'=> $configpay,
    // 發送短信--阿里雲配置
    'ALI_SMS'=>[
        'TemplateCode'=>[
            1=>'SMS_163440685',// 首次綁定手機號
            2=>'SMS_163440685'// 修改手機號
        ],
        'SignName'=>'b',
        'accessKeyId'=>'LTAIADRWKccOMa6N',
        'accessKeySecret'=>'MDSWxxrFrXFljvniqTEEHRKuTUrgr4',
    ],
    'WEB_URL'=>"http://chitu.56cold.com/",// 前端鏈接 todo
    'PORT_URL'=>"http://twb.com/",// 接口鏈接 todo
    'TOKEN_SIGN_STR'=> "%^&&*@#W$$%%^&*()_+",// 前端 token 添加的密碼鹽 todo
    'SIGN_STR'=> "!@##$%%^&!@3",// 發送郵件認證碼時添加的密碼鹽 todo
];