import React from "react";

import './BookReviewInput.scss';
import { useForm } from "react-hook-form";



export const BookReviewInput = (props) => {
    const { register, formState: { errors } } = useForm(); // バリデーションのフォームを定義。
    const [bookTitle, setBookTitle] = useState('');
    const [bookUrl, setBookUrl] = useState('');
    const [bookDetail, setBookDetail] = useState('');
    const [bookReview, setBookReview] = useState('');

    const handleTitleChange = (e) => props.setBookTitle(e.target.value);
    const handleUrlChange = (e) => props.setBookUrl(e.target.value);
    const handleDetailChange = (e) => props.setBookDetail(e.target.value);
    const handleReviewChange = (e) => props.setBookReview(e.target.value);

    return(
        <div>
            <label>タイトル</label>
            <br />
            <input
                type="text"
                {...register("title", {required: true})}
                onChange={handleTitleChange}
                className="input_title"
                defaultValue={props.bookData.title}
            />
            <p>{errors.title?.type === 'required' && <b className='error-message'>※タイトルを入力してください。</b>}</p>
            <label>URL</label>
            <br />
            <input
                type="text" 
                {...register("url", {required: true})}
                onChange={handleUrlChange}
                className="input_url"
                defaultValue={props.bookData.url}
            />
            <p>{errors.url?.type === 'required' && <b className='error-message'>※書籍URLを入力してください。</b>}</p>                
            <label>書籍の詳細情報</label>
            <br />
            <textarea
                onChange={handleDetailChange}
                className="input_detail"
                defaultValue={props.bookData.detail}
            />
            <br />
            <label>書籍のレビュー</label>
            <br />
            <textarea
                {...register("review", {required: true})}
                onChange={handleReviewChange}
                className="input_review"
                defaultValue={props.bookData.review}
            />
            <p>{errors.review?.type === 'required' && <b className='error-message'>※書籍レビューを入力してください。</b>}</p>
        </div>
    )
}