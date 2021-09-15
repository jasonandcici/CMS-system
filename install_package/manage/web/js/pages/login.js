// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/4/4.
// +----------------------------------------------------------------------

/**
 * login
 * */

var loginApp = function () {
  /**
   * 初始化
   * */

  var _initFun = function () {
    var $verifyCode = $('#j_verify_code');
    $verifyCode.parent().find('input').attr('placeholder',$verifyCode.data('placeholder'));

    // 工具提示
    $verifyCode.add('.j_tooltip').tooltip();


    $('#j_form').on('beforeSubmit',function(e){
      var $form = $(this),
        $submit = $form.find(':submit');
        if($form.hasClass('disabled')) return true;
        $form.addClass('disabled');

      $.ajax({
        type: $form.attr('method'),
        url: $form.attr('action'),
        data: $form.serialize(),
        dataType: 'json',
        beforeSend: function (XMLHttpRequest) {
          $submit.button('loading');
        },
        complete: function () {
            $form.removeClass('disabled');
            $form.data('yiiActiveForm').validated = false;
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          $submit.button('reset');
          setError($form,errorThrown+' - '+ textStatus);
            $('#loginform-captcha-image').trigger('click');
        },
        success: function (result) {
          if(result.status == 1){
            $submit.button('jump');
            commonApp.inFrame(function () {
              localStorage.removeItem('history');
              parent.history.go(0);
            },function () {
              window.location.href = result.jumpLink;
            });
          }else{
            setError($form,result.message);
            $submit.button('reset');
              $('#loginform-captcha-image').trigger('click');
          }
        }
      });
    }).on('submit', function (e) {
      e.preventDefault();
    });

    $('#j_enter_system').click(function () {
      var $this = $(this);
      commonApp.inFrame(function () {
        window.location.href = $(parent.document).find('.brand').data('welcome');
      },function () {
        window.location.href = $this.attr('href');
      });
      return false;
    });
  };

  /**
   * 设置错误
   * @param $form
   * @param error
   */
  function setError($form,error){
    var $alert = $form.prev('.alert');
    if($alert.size()<1){
      $alert = $('<div class="alert alert-danger"></div>');
      $form.before($alert);
    }
    $alert.html('<span class="iconfont">&#xe60d;</span>'+ error);

    //$('#j_verify_code').find('img').trigger('click');
  }

  return {
    init: function () {
      _initFun();
    }
  }
}();
