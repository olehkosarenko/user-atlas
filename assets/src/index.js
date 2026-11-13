import { createRoot, StrictMode } from '@wordpress/element';
import App from './App';

const root = createRoot( document.getElementById( 'root' ) );
root.render(
	<StrictMode>
		<App />
	</StrictMode>
);
