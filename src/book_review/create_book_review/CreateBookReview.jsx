import React, { useState } from 'react';
import { useCookies } from 'react-cookie';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import { BookReviewInput } from '../book_review_input/BookReviewInput';
import { Header } from '../header/Header';
import './CreateBookReview.scss';

export const CreateBookReview = () => {
  const navigate = useNavigate();
  const [cookies] = useCookies();
  const [bookTitle, setBookTitle] = useState('');
  const [bookUrl, setBookUrl] = useState('');
  const [bookDetail, setBookDetail] = useState('');
  const [bookReview, setBookReview] = useState('');
  const [isSpoiler, setIsSpoiler] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const createBookUrl = useUrl('bookOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const [bookData] = useState([]);

  const createBook = () => {
    const data = {
      title: bookTitle,
      url: bookUrl,
      detail: bookDetail,
      review: bookReview,
      isSpoiler: isSpoiler
    };

    axios
      .post(createBookUrl, data, {
        headers: {
          authorization: `Bearer ${cookies.token}`
        }
      })
      .then(() => {
        navigate('/');
      })
      .catch((error) => {
        setErrorMessage(`エラー発生 ${error}`);
      });
  };

  return (
    <div className="page">
      <Header />
      <h1 className="create_book_h1">書籍レビュー新規投稿</h1>
      <h2 className="error-massage">{errorMessage}</h2>
      <BookReviewInput
        bookData={bookData}
        isSpoiler={isSpoiler}
        setBookTitle={setBookTitle}
        setBookUrl={setBookUrl}
        setBookDetail={setBookDetail}
        setBookReview={setBookReview}
        setIsSpoiler={setIsSpoiler}
        BookOperations={createBook}
      />
    </div>
  );
};
