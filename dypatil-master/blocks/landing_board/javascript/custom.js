M.util.init_block_landing_board = function(Y,args) {

$(args.id).qtip({
content: {
 text: function(event, api) {
        $.ajax({
	 url: M.cfg.wwwroot+'/blocks/landing_board/ajax.php',
         data: {'schoolid': args.schoolid,'type':args.type}
	})
	.then(function(content){
       console.log(content);
	api.set('content.text', content);
	},
	function(xhr, status, error) {
              console.log(error);

	    // Upon failure... set the tooltip content to the status and error value
	api.set('content.text', status + ': ' + error);
	});
      return 'Loading...';
      }
   },
 hide: {
    fixed: true,
    delay: 100
    }
});
}