import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
	const blockProps = useBlockProps.save();

	return (
		<pre {...blockProps}>
			<code>{attributes.code}</code>
		</pre>
	);
}