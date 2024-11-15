import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import hljs from 'highlight.js';
import 'highlight.js/styles/default.css';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();

	// Применяем подсветку при каждом изменении контента
	useEffect(() => {
		document.querySelectorAll('.highlighted-code').forEach((el) => {
			hljs.highlightElement(el);
		});
	}, [attributes.content]);

	return (
		<div {...blockProps}>
			<pre className="highlighted-code">
				<RichText
					tagName="code"
					value={attributes.content}
					onChange={(content) => setAttributes({ content })}
					placeholder="Введите код..."
					allowedFormats={[]} // Отключаем форматы
				/>
			</pre>
		</div>
	);
}
