import React from "react";
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { SignUp } from "../book_review/signup/SignUp";
import { Login } from "../book_review/login/Login";
import { Home } from "../book_review/home/Home";
import { EditProfile } from "../book_review/edit_profile/EditProfile";
import { CreateBookReview } from "../book_review/create_book_review/CreateBookReview";
import { BookReviewDetail } from "../book_review/book_review_detail/BookReviewDetail";
import { EditBookReview } from "../book_review/edit_book_review/EditBookReview";

export const Router = () => {

    return(
        <BrowserRouter>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/signup" element={<SignUp />} />
                <Route path="/" element={<Home />} />
                <Route path="/profile" element={<EditProfile />} />
                <Route path="/new" element={<CreateBookReview />} />
                <Route path="/detail/:BookId" element={<BookReviewDetail />} />
                <Route path="/edit/:BookId" element={<EditBookReview />} />
            </Routes>
        </BrowserRouter>
    )
}