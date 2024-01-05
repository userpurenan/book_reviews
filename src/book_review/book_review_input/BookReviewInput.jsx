import React from 'react';
import { useForm } from 'react-hook-form';
import PropTypes from 'prop-types';
import '../create_book_review/CreateBookReview.scss';
import '../edit_book_review/EditBookReview.jsx';
import './BookReviewInput.scss';

export const BookReviewInput = (props) => {
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。

  const handleTitleChange = (e) => props.setBookTitle(e.target.value);
  const handleUrlChange = (e) => props.setBookUrl(e.target.value);
  const handleDetailChange = (e) => props.setBookDetail(e.target.value);
  const handleReviewChange = (e) => props.setBookReview(e.target.value);

  return (
    <div className='float_component'>
      <form onSubmit={handleSubmit(props.BookOperations)} className="book_operation">
        <label className='title'>タイトル</label>
        <br />
        <input
          type="text"
          {...register('title', { required: true })}
          onChange={handleTitleChange}
          className="input_title"
          defaultValue={props.bookData.title}
        />
        <p>{errors.title?.type === 'required' && <b className="error-message">※タイトルを入力してください。</b>}</p>
        <label className='url'>URL</label>
        <br />
        <input
          type="text"
          {...register('url', { required: true })}
          onChange={handleUrlChange}
          className="input_url"
          defaultValue={props.bookData.url}
        />
        <p>{errors.url?.type === 'required' && <b className="error-message">※書籍URLを入力してください。</b>}</p>
        <label className='detail_info'>書籍の詳細情報</label>
        <br />
        <textarea onChange={handleDetailChange} className="input_detail" defaultValue={props.bookData.detail} />
        <br />
        <label className='review'>書籍のレビュー</label>
        <br />
        <textarea
          {...register('review', { required: true })}
          onChange={handleReviewChange}
          className="input_review"
          defaultValue={props.bookData.review}
        />
        <p>
          {errors.review?.type === 'required' && <b className="error-message">※書籍レビューを入力してください。</b>}
        </p>
        {props.BookOperations.name === 'createBook' ? (
          <button type="submit" className="createBook__button">
            作成
          </button>
        ) : (
          <div>
            <button onClick={props.deleteBook} className="delete-button">
              削除
            </button>
            <button type="submit" className="EditBook__button">
              更新
            </button>
          </div>
        )}
      </form>
    </div>
  );
};

BookReviewInput.propTypes = {
  setBookTitle: PropTypes.func.isRequired,
  setBookUrl: PropTypes.func.isRequired,
  setBookDetail: PropTypes.func.isRequired,
  setBookReview: PropTypes.func.isRequired,
  BookOperations: PropTypes.object.isRequired,
  deleteBook: PropTypes.func.isRequired,
  bookData: PropTypes.shape({
    title: PropTypes.string,
    url: PropTypes.string,
    detail: PropTypes.string,
    review: PropTypes.string
  })
};
