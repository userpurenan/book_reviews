import React from 'react';
import { useSelector } from 'react-redux';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { BookReviewDetail } from '../book_review/book_review_detail/BookReviewDetail';
import { CreateBookReview } from '../book_review/create_book_review/CreateBookReview';
import { EditBookReview } from '../book_review/edit_book_review/EditBookReview';
import { EditProfile } from '../book_review/edit_profile/EditProfile';
import { ErrorPage } from '../book_review/error_page/ErrorPage';
import { Home } from '../book_review/home/Home';
import { Login } from '../book_review/login/Login';
import { SignUp } from '../book_review/signup/SignUp';
import { AccountDelete } from '../book_review/account_delete/AccountDelete';

export const Router = () => {
  const auth = useSelector((state) => state.auth.isSignIn);
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/signup" element={<SignUp />} />
        <Route path="/" element={<Home />} />
        {auth ? (
          <>
            <Route path="/edit/profile" element={<EditProfile />} />
            <Route path="/new" element={<CreateBookReview />} />
            <Route path="/detail/:BookId" element={<BookReviewDetail />} />
            <Route path="/edit/:BookId" element={<EditBookReview />} />
            <Route path="/delete/account" element={<AccountDelete />} />
          </>
        ) : (
          <Route path="*" element={<ErrorPage />} />
        )}
      </Routes>
    </BrowserRouter>
  );
};
