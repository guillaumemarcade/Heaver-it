/*************************************************************************

    This file is part of Heaver-it.

    Heaver-it is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Heaver-it is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Heaver-it. If not, see <http://www.gnu.org/licenses/>.
    
    Author : moins52 (moins52@yahoo.fr)
    Project home: http://www.k-metamodule.com/heaver-it/
    
*************************************************************************/

(function($){
    // Default values
    $.taskify = {
        defaults: {
          // Nothing
        }
    }
            
    $.fn.taskify = function(options) {
        var options = $.extend({}, $.taskify.defaults, options);
      
        return this.each(function() {
            var $$ = $(this);
            
            // Makes tasks clickable
            $$.click(function() {
                $(".task").trigger("putTaskOnTop", [$$.css('zIndex')]);
                $( ".actionlistener" ).trigger("modification");
            });
            
            // Puts task on top
            $$.bind("putTaskOnTop", function(event, zIndex) {
                // Lows tasks currently on higher levels 
                if (zIndex < $$.css('zIndex')) {
                  $$.css({
                    zIndex: $$.css('zIndex') - 1
                  });
                }
                // Puts the one task on top
                else if (zIndex == $$.css('zIndex')) {
                  $$.css({
                    zIndex: $(".task").length - 1
                  });
                }
            });

            // Makes tasks draggable
            $$.draggable({
              scroll: true,
              start: function() {
                $(".task").trigger("putTaskOnTop", [$$.css('zIndex')]);
                $( ".actionlistener" ).trigger("modification");
                
                originLeft = this.style.left;
                originTop = this.style.top;
              } 
            });

            // Makes tasks editable
            $$.editable({
              type:'textarea',
              editBy:'dblclick',
              submitBy:'blur',
          		onEdit: function(content) {
                  $$.css({
                      overflow: 'hidden',
                      padding: '0px',
                      width: '150px',
                      height: '150px'
                  });
                  
                  // Replace all HTML line breaks by newlines
                  var value = $('textarea').val().replace(/(<br>)|(<br \/>)|(<p>)|(<\/p>)/g, "\n");
                  $('textarea').val(value);
          		},
          		onSubmit: function(content) {
                  $$.css({
                      overflow: 'auto',
                      padding: '2px',
                      width: '146px',
                      height: '146px'
                  });
                  
                  // Replace all newlines by HTML line breaks
                  var html = $$.html().replace(/(\r\n|\n\r|\r|\n)/g, '<br />');
                  $$.html(html);
                  
                  $( ".actionlistener" ).trigger("modification");
          		}
            });
        });
    };
    
})(jQuery);