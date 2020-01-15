fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery[ 'fbuilder' ] = fbuilderjQuery[ 'fbuilder' ] || {};
fbuilderjQuery[ 'fbuilder' ][ 'modules' ] = fbuilderjQuery[ 'fbuilder' ][ 'modules' ] || {};

fbuilderjQuery[ 'fbuilder' ][ 'modules' ][ 'processing' ] = {
	'tutorial' : 'https://cff.dwbooster.com/documentation#managing-fields-module',
	'toolbars'		: {
		'processing' : {
			'label' : 'Managing fields',
			'buttons' : [
							{
								"value" : "getField",
								"code" : "getField(",
								"tip" : "<p>Get the field object. <strong>getField( # or fieldname# )</strong></p><p>Returns the internal representation of a field object. For example, if there is the slider field: fieldname1, to assing it a value, for example:50, enter as part of the equation associated to the calculated field the piece of code: getField(1).setVal(50);</p><p>The getField operation can be used only in the context of the equations.</p>"
							}
						]
		}
	}
};