var onZoom = function() {
  var zoomin = Y.one('body').hasClass('zoomin');
  if (zoomin) {
    Y.one('body').removeClass('zoomin');
    M.util.set_user_preference('theme_essential_zoom', 'nozoom');
  } else {
    Y.one('body').addClass('zoomin');
    M.util.set_user_preference('theme_essential_zoom', 'zoomin');
  }
};

//When the button with class .moodlezoom is clicked fire the onZoom function
M.theme_essential = M.theme_essential || {};
M.theme_essential.zoom =  {
  init: function() {
    Y.one('body').delegate('click', onZoom, '.moodlezoom');
  }
};