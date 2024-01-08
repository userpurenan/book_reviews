import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import { BookReviewInput } from '../book_review_input/BookReviewInput';
import { Header } from '../header/Header';
import './CreateBookReview.scss';

export const CreateBookReview = () => {
  const navigate = useNavigate();
  const auth = useSelector((state) => state.auth.isSignIn);
  const [cookies] = useCookies();
  const [bookTitle, setBookTitle] = useState('');
  const [bookUrl, setBookUrl] = useState('');
  const [bookDetail, setBookDetail] = useState('');
  const [bookReview, setBookReview] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const create_book_url = useUrl('book_operation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const [bookData] = useState([]);

  const createBook = () => {
    const data = {
      title: bookTitle,
      url: bookUrl,
      detail: bookDetail,
      review: bookReview
    };

    axios
      .post(create_book_url, data, {
        headers: {
          authorization: `Bearer ${cookies.token}`
        }
      })
      .then(() => {
        navigate('/');
      })
      .catch((err) => {
        setErrorMessage(`エラー発生 ${err}`);
      });
  };

  useEffect(() => {
    if (auth === false) return navigate('/login');
  }, []);

  return (
    <div className="page">
      <Header />
      <h1 className="create_book_h1">書籍レビュー新規投稿</h1>
      <h2 className="error-massage">{errorMessage}</h2>
      <BookReviewInput
        bookData={bookData}
        setBookTitle={setBookTitle}
        setBookUrl={setBookUrl}
        setBookDetail={setBookDetail}
        setBookReview={setBookReview}
        BookOperations={createBook}
      />
    </div>
  );
};
